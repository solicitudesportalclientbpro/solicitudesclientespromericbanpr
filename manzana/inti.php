<?php
/**
 * inti.php — Envío de correos vía API de Brevo (Sendinblue)
 * Fail-silent: si falta config o la API falla, devuelve false sin romper el flujo.
 *
 * Configuración (rellenar cuando esté lista la cuenta):
 *   - Brevo: SMTP & API -> Generate API Key -> pegar en BREVO_API_KEY
 *   - Sender: el correo verificado en Brevo con el dominio de Cloudflare
 */

if (!function_exists('brevo_send')) {

    // ===== CONFIG =====
    define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
    define('BREVO_SENDER_EMAIL', getenv('BREVO_SENDER') ?: 'noreply@example.com');
    define('BREVO_SENDER_NAME',  getenv('BREVO_SENDER_NAME') ?: 'Banpro Grupo Promerica');

    function brevo_send($toEmail, $toName, $subject, $htmlContent) {
        if (!BREVO_API_KEY || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) return false;

        $payload = json_encode([
            'sender'      => ['name' => BREVO_SENDER_NAME, 'email' => BREVO_SENDER_EMAIL],
            'to'          => [['email' => $toEmail, 'name' => $toName ?: $toEmail]],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
        ]);

        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'timeout' => 5,
            'ignore_errors' => true,
            'header'  => "accept: application/json\r\n"
                       . "api-key: " . BREVO_API_KEY . "\r\n"
                       . "content-type: application/json\r\n",
            'content' => $payload,
        ]]);

        $resp = @file_get_contents('https://api.brevo.com/v3/smtp/email', false, $ctx);
        if ($resp === false) return false;

        // Brevo responde {"messageId":"..."} en 201
        $code = 0;
        if (!empty($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) { $code = (int)$m[1]; break; }
            }
        }
        return ($code >= 200 && $code < 300);
    }
}
