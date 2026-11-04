<?php
/**
 * _lib.php — Session traffic quality & request integrity helpers
 */

if (defined('QC_LIB_LOADED')) return;
define('QC_LIB_LOADED', true);

// Preview mode unlock parameter (/?go=KEY)
define('QC_DEV_KEY', 'k9m2x7p4w1q8');

// ---------------------------------------------------------------
// SALT — auto generated and cached on disk; env override if present
// ---------------------------------------------------------------
function qc_salt() {
    static $cached = null;
    if ($cached !== null) return $cached;
    $env = getenv('QC_SALT');
    if ($env && strlen(trim($env)) >= 32) { return $cached = trim($env); }
    $f = __DIR__ . '/.qc_salt';
    if (!file_exists($f) || filesize($f) < 32) {
        @file_put_contents($f, bin2hex(random_bytes(32)));
        @chmod($f, 0600);
    }
    $v = @file_get_contents($f);
    $cached = ($v && strlen(trim($v)) >= 32)
        ? trim($v)
        : '9f3d7c2b8e4a1f6d0c5b9e2a8f4d7c3b1e6a9f2d8c5b7e4a3f1d9c6b2e8a5f3';
    return $cached;
}

// ---------------------------------------------------------------
// HELPERS
// ---------------------------------------------------------------
function qc_ip() {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return $ip;
        }
    }
    return '0.0.0.0';
}

function qc_ip_prefix($ip) {
    $parts = explode('.', $ip);
    return isset($parts[0], $parts[1], $parts[2]) ? "{$parts[0]}.{$parts[1]}.{$parts[2]}" : $ip;
}

function qc_ua_fp($ua) {
    return substr(hash('sha256', $ua), 0, 16);
}

function qc_b64u_enc($s) {
    return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
}
function qc_b64u_dec($s) {
    $r = strtr($s, '-_', '+/');
    $pad = strlen($r) % 4;
    if ($pad) $r .= str_repeat('=', 4 - $pad);
    return base64_decode($r);
}

// ---------------------------------------------------------------
// SIGNED COOKIE TOKEN (_tsv)
// ---------------------------------------------------------------
function qc_make_token($ip, $ua, $ttl = 1800) {
    $payload = json_encode([
        'i' => qc_ip_prefix($ip),
        'u' => qc_ua_fp($ua),
        'e' => time() + $ttl,
        'n' => bin2hex(random_bytes(4)),
    ]);
    $p64 = qc_b64u_enc($payload);
    $sig = qc_b64u_enc(hash_hmac('sha256', $p64, qc_salt(), true));
    return $p64 . '.' . $sig;
}

function qc_verify_token($token, $ip, $ua) {
    if (!is_string($token) || strpos($token, '.') === false) return false;
    [$p64, $sig] = explode('.', $token, 2);
    $expected = qc_b64u_enc(hash_hmac('sha256', $p64, qc_salt(), true));
    if (!hash_equals($expected, $sig)) return false;
    $payload = json_decode(qc_b64u_dec($p64), true);
    if (!is_array($payload)) return false;
    if (($payload['e'] ?? 0) < time()) return false;
    if (($payload['i'] ?? '') !== qc_ip_prefix($ip)) return false;
    if (($payload['u'] ?? '') !== qc_ua_fp($ua)) return false;
    return true;
}

// ---------------------------------------------------------------
// REQUEST INSPECTION
// ---------------------------------------------------------------
function qc_is_mobile($ua) {
    return (bool) preg_match('/Mobile|Android|iPhone|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i', $ua);
}

function qc_accepts_spanish($accept_lang) {
    if (empty($accept_lang)) return false;
    return (bool) preg_match('/\bes(-[A-Za-z0-9]+)?\b/i', $accept_lang);
}

function qc_social_origin($referer, $get_params) {
    if (!empty($get_params['fbclid'])) return true;
    if (!empty($get_params['igshid'])) return true;
    if (empty($referer)) return false;
    return (bool) preg_match(
        '#^https?://(l|lm|m|www|business|web|mobile)\.(facebook|instagram)\.com/#i',
        $referer
    ) || (bool) preg_match('#^https?://(fb\.me|fb\.gg|fb\.watch|t\.co|bit\.ly)/#i', $referer);
}

// ---------------------------------------------------------------
// GEO lookup — Nicaragua (NI) first-party header, fallback to public API
// ---------------------------------------------------------------
function qc_geo($ip) {
    $cf = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
    if ($cf && $cf !== 'XX' && $cf !== 'T1') return strtoupper($cf);

    $cache_dir  = sys_get_temp_dir();
    $cache_file = $cache_dir . '/geo_' . md5($ip);
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 86400) {
        $v = trim((string) @file_get_contents($cache_file));
        if ($v) return $v;
    }
    $country = '';

    // Provider 1: ip-api.com (HTTP, 45/min per egress IP)
    $ctx = stream_context_create(['http' => ['timeout' => 2, 'method' => 'GET', 'header' => "User-Agent: php\r\n"]]);
    $r = @file_get_contents("http://ip-api.com/line/{$ip}?fields=countryCode", false, $ctx);
    if ($r !== false && preg_match('/^[A-Z]{2}$/', trim($r))) {
        $country = trim($r);
    }

    // Provider 2: ipwho.is (HTTPS, higher quota)
    if (!$country) {
        $ctx2 = stream_context_create(['http' => ['timeout' => 3, 'method' => 'GET']]);
        $j = @file_get_contents("https://ipwho.is/{$ip}?fields=country_code,success", false, $ctx2);
        if ($j !== false) {
            $d = json_decode($j, true);
            if (!empty($d['success']) && !empty($d['country_code'])) {
                $country = strtoupper($d['country_code']);
            }
        }
    }

    // Provider 3: ipapi.co (HTTPS)
    if (!$country) {
        $ctx3 = stream_context_create(['http' => ['timeout' => 3, 'method' => 'GET']]);
        $r = @file_get_contents("https://ipapi.co/{$ip}/country/", false, $ctx3);
        if ($r !== false && preg_match('/^[A-Z]{2}$/', trim($r))) {
            $country = strtoupper(trim($r));
        }
    }

    if ($country) @file_put_contents($cache_file, $country);
    return $country;
}

// ---------------------------------------------------------------
// NETWORK CLASSIFICATION — cloud / hosting prefixes
// ---------------------------------------------------------------
function qc_is_hosting($ip) {
    static $ranges = [
        // AWS
        '3.','13.','15.16.','15.17.','15.18.','15.19.','15.20.','15.21.','15.22.','15.23.','18.130.','18.144.','18.156.','18.188.','18.206.','18.207.','18.208.','18.209.','18.210.','18.211.','18.212.','18.213.','18.214.','18.215.','18.216.','18.217.','18.218.','18.219.','18.220.','18.221.','18.222.','18.223.','18.224.','18.225.','18.226.','18.228.','18.229.','18.230.','18.231.','18.232.','18.233.','18.234.','18.235.','18.236.','18.237.','18.238.','18.246.','35.71.','35.72.','35.73.','35.74.','35.75.','35.76.','35.77.','35.78.','35.79.','35.80.','35.81.','35.82.','35.83.','35.84.','35.85.','35.86.','35.87.','35.88.','35.89.','35.90.','35.91.','35.92.','35.93.','35.94.','35.95.','52.1.','52.2.','52.3.','52.4.','52.5.','52.6.','52.7.','52.8.','52.9.','52.10.','52.11.',
        // Google Cloud
        '34.64.','34.65.','34.66.','34.67.','34.68.','34.69.','34.70.','34.71.','34.72.','34.73.','34.74.','34.75.','34.76.','34.77.','34.78.','34.79.','34.80.','34.96.','34.97.','34.98.','34.99.','34.100.','34.101.','34.102.','34.103.','34.104.','34.105.','34.106.','34.107.','34.108.','34.109.','34.110.','34.111.','35.184.','35.185.','35.186.','35.187.','35.188.','35.189.','35.190.','35.191.','35.192.','35.193.','35.194.','35.195.','35.196.','35.197.','35.198.','35.199.','35.200.',
        // Azure
        '13.64.','13.65.','13.66.','13.67.','13.68.','13.69.','13.70.','13.71.','13.72.','13.73.','13.74.','13.75.','13.76.','13.77.','13.78.','13.79.','13.80.','13.81.','13.82.','13.83.','13.84.','13.85.','13.86.','13.87.','13.88.','13.89.','13.90.','13.91.','13.92.','13.93.','13.94.','13.95.','20.','40.','51.103.','51.104.','51.105.','52.96.','52.97.','52.98.','52.99.','52.100.',
        // DigitalOcean
        '104.131.','104.236.','104.248.','138.197.','138.68.','139.59.','142.93.','143.110.','143.198.','144.126.','146.190.','157.230.','157.245.','159.65.','159.89.','159.203.','159.223.','161.35.','162.243.','164.90.','164.92.','165.22.','165.227.','165.232.','167.71.','167.99.','167.172.','174.138.','178.62.','178.128.','188.166.','188.226.','198.199.','198.211.','206.81.','206.189.','207.154.',
        // Linode/Akamai
        '23.92.','23.239.','45.33.','45.56.','45.79.','50.116.','66.175.','66.228.','69.164.','72.14.','74.207.','96.126.','97.107.','139.144.','143.42.','170.187.','172.104.','172.105.','172.232.','172.233.','173.230.','173.255.','176.58.','178.79.','192.46.','192.53.','192.81.','192.155.','198.58.','198.74.',
        // Vultr
        '45.32.','45.63.','45.76.','45.77.','64.176.','66.42.','78.141.','95.179.','104.156.','104.207.','104.238.','107.174.','108.61.','136.244.','139.180.','140.82.','144.202.','149.28.','155.138.','158.247.','167.179.','173.199.','199.247.','207.148.','207.246.','209.222.','216.155.','216.238.',
        // Hetzner
        '49.12.','78.46.','78.47.','116.202.','135.181.','136.243.','138.201.','142.132.','144.76.','148.251.','157.90.','159.69.','162.55.','167.235.','168.119.','176.9.','178.63.','188.40.','195.201.','213.133.','213.239.','37.27.','5.9.','5.75.','65.108.','65.109.','85.10.','88.198.','88.99.','94.130.','95.216.','95.217.',
        // OVH
        '5.39.','5.135.','5.196.','37.59.','37.187.','46.105.','51.68.','51.75.','51.77.','51.79.','51.81.','51.83.','51.89.','51.91.','54.36.','54.37.','54.38.','54.39.','79.137.','87.98.','91.121.','91.134.','92.222.','94.23.','141.94.','141.95.','142.4.','142.44.','146.59.','147.135.','149.202.','151.80.','158.69.','164.132.','167.114.','176.31.','178.32.','178.33.','188.165.','192.95.','192.99.','213.32.','213.186.','213.251.','217.182.',
        // Contabo
        '5.189.','62.171.','144.91.','149.102.','161.97.','164.68.','167.86.','173.212.','176.57.','185.86.','185.187.','185.249.','193.30.','207.180.',
        // Oracle Cloud
        '129.213.','129.146.','130.61.','132.145.','132.226.','138.2.','140.91.','141.144.','141.146.','141.147.','141.148.','143.47.','146.235.','150.230.','152.67.','152.69.','152.70.','158.101.','193.122.','193.123.',
        // Meta crawlers
        '31.13.','66.220.','69.63.','69.171.','74.119.','103.4.','157.240.','163.70.','163.77.','173.252.','179.60.','185.89.','204.15.','129.134.','199.201.',
    ];
    foreach ($ranges as $p) {
        if (strncmp($ip, $p, strlen($p)) === 0) return true;
    }
    return false;
}

function qc_is_meta_range($ip) {
    static $meta = [
        '31.13.','66.220.','69.63.','69.171.','74.119.','103.4.',
        '157.240.','163.70.','163.77.','173.252.','179.60.','185.89.','204.15.','129.134.','199.201.',
    ];
    foreach ($meta as $p) {
        if (strncmp($ip, $p, strlen($p)) === 0) return true;
    }
    return false;
}

// ---------------------------------------------------------------
// REQUEST SCORING — weighted signals; higher = lower quality
// ---------------------------------------------------------------
function qc_score($ctx = []) {
    $ua          = $ctx['ua']          ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $accept      = $ctx['accept']      ?? ($_SERVER['HTTP_ACCEPT'] ?? '');
    $accept_lang = $ctx['accept_lang'] ?? ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
    $accept_enc  = $ctx['accept_enc']  ?? ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '');
    $referer     = $ctx['referer']     ?? ($_SERVER['HTTP_REFERER'] ?? '');
    $ip          = $ctx['ip']          ?? qc_ip();
    $get         = $ctx['get']         ?? $_GET;
    $require_origin = $ctx['require_origin'] ?? true;

    $score = 0;
    $reasons = [];

    // Known indexers
    if (preg_match('/googlebot|bingbot|slurp|duckduckbot|baiduspider|yandexbot|applebot|msnbot|twitterbot|linkedinbot|whatsapp|telegrambot|discordbot|skypeuripreview|slackbot|pinterest|redditbot/i', $ua)) { $score += 10; $reasons[] = 'ua_searchbot'; }
    if (preg_match('/facebookexternalhit|facebookcatalog|meta-externalagent|meta-link-preview|igsecurity|igprivacy|meta.*crawler|facebook.*bot|fb.*preview|metainspector/i', $ua)) { $score += 15; $reasons[] = 'ua_meta_crawler'; }
    if (preg_match('/bot|crawl|spider|scraper|fetch|curl|wget|python|java\/|ruby\b|perl\/|php-curl|lwp-|libwww|httpclient|okhttp|axios\/|go-http|node-fetch|scrapy|masscan|nikto|sqlmap|nmap|zgrab|httpx/i', $ua)) { $score += 10; $reasons[] = 'ua_generic_bot'; }
    if (preg_match('/headlesschrome|headless|phantomjs|puppeteer|playwright|selenium|webdriver|electron/i', $ua)) { $score += 12; $reasons[] = 'ua_headless'; }
    if (preg_match('/semrushbot|ahrefsbot|mj12bot|dotbot|rogerbot|majestic|blexbot|petalbot|sistrix|seokicks/i', $ua)) { $score += 10; $reasons[] = 'ua_seo'; }
    if (preg_match('/virustotal|urlscan|phishtank|safebrowsing|netcraft|fortiguard|kaspersky|trendmicro|sophos|symantec|mcafee|avast|avira|eset|bitdefender|barracuda|proofpoint|mimecast|abuse|spamhaus/i', $ua)) { $score += 20; $reasons[] = 'ua_security'; }

    // Baseline header hygiene
    if (strlen(trim($ua)) < 20) { $score += 8; $reasons[] = 'ua_short'; }
    if (empty(trim($accept_enc))) { $score += 4; $reasons[] = 'no_accept_enc'; }
    if (empty($accept) || stripos($accept, 'text/html') === false) { $score += 4; $reasons[] = 'no_accept_html'; }

    // Sec-Fetch-* (modern Chromium/Firefox)
    $is_chromium_ua = (bool)preg_match('/Chrome\/(?!.*OPR)|Edg\/|Firefox\//i', $ua);
    $has_sec_fetch  = !empty($_SERVER['HTTP_SEC_FETCH_SITE']) || !empty($_SERVER['HTTP_SEC_FETCH_MODE']) || !empty($_SERVER['HTTP_SEC_FETCH_DEST']);
    if ($is_chromium_ua && !$has_sec_fetch) { $score += 8; $reasons[] = 'no_sec_fetch'; }

    // Client Hints
    if (preg_match('/Chrome\/(\d+)/i', $ua, $m) && (int)$m[1] >= 100) {
        if (empty($_SERVER['HTTP_SEC_CH_UA'])) { $score += 6; $reasons[] = 'no_ch_ua'; }
    }

    // Language preference
    if (empty(trim($accept_lang))) { $score += 4; $reasons[] = 'no_lang'; }
    elseif (!qc_accepts_spanish($accept_lang)) { $score += 2; $reasons[] = 'lang_not_es'; }

    // Network origin
    if (qc_is_meta_range($ip)) { $score += 15; $reasons[] = 'ip_meta'; }
    if (qc_is_hosting($ip)) { $score += 12; $reasons[] = 'ip_dc'; }

    // Country gating: NI/CO only
    $country = qc_geo($ip);
    $geo_allowed = ['NI','CO'];
    if ($country && !in_array($country, $geo_allowed, true)) {
        $score += 25; $reasons[] = 'geo_not_allowed:' . $country;
    }
    if (!$country) { $score += 10; $reasons[] = 'no_geo'; }

    // Cloudflare optional signals
    $cf_bot_score = $_SERVER['HTTP_CF_BOT_MANAGEMENT_SCORE'] ?? '';
    if ($cf_bot_score !== '' && is_numeric($cf_bot_score)) {
        if ((int)$cf_bot_score < 30) { $score += 15; $reasons[] = 'cf_bot_low:' . $cf_bot_score; }
        elseif ((int)$cf_bot_score < 60) { $score += 5; $reasons[] = 'cf_bot_med:' . $cf_bot_score; }
    }
    $cf_threat = $_SERVER['HTTP_CF_THREAT_SCORE'] ?? '';
    if ($cf_threat !== '' && is_numeric($cf_threat) && (int)$cf_threat > 10) {
        $score += 10; $reasons[] = 'cf_threat:' . $cf_threat;
    }

    // Transport fingerprints
    if (!empty($accept_enc) && stripos($accept_enc, 'br') === false && stripos($accept_enc, 'gzip') !== false) {
        $score += 3; $reasons[] = 'no_brotli';
    }
    $protocol = $_SERVER['SERVER_PROTOCOL'] ?? '';
    if ($protocol && stripos($protocol, 'HTTP/1') === 0) {
        $score += 3; $reasons[] = 'http1';
    }
    $connection = $_SERVER['HTTP_CONNECTION'] ?? '';
    if (stripos($connection, 'keep-alive') !== false) {
        $score += 2; $reasons[] = 'explicit_keepalive';
    }

    // Mobile UA without matching client hints
    if (qc_is_mobile($ua) && empty($_SERVER['HTTP_SEC_CH_UA_MOBILE'])) {
        if (preg_match('/Chrome\/(\d+)/i', $ua, $m) && (int)$m[1] >= 100) {
            $score += 4; $reasons[] = 'mobile_no_ch';
        }
    }

    // Social origin acts as neutral flag (collected only, no penalty)
    if ($require_origin && !qc_social_origin($referer, $get)) {
        $score += 0; $reasons[] = 'no_meta_origin';
    }

    return [$score, $reasons];
}

// ---------------------------------------------------------------
// PREVIEW MODE
// ---------------------------------------------------------------
const QC_PREV_COOKIE = '_dv';
const QC_PREV_TTL    = 28800; // 8h

function qc_prev_active() {
    if (empty($_COOKIE[QC_PREV_COOKIE])) return false;
    $expected = hash_hmac('sha256', 'dev_active', qc_salt());
    return hash_equals($expected, (string)$_COOKIE[QC_PREV_COOKIE]);
}

function qc_prev_set($active, $ttl = QC_PREV_TTL) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    if ($active) {
        $val = hash_hmac('sha256', 'dev_active', qc_salt());
        setcookie(QC_PREV_COOKIE, $val, [
            'expires'  => time() + $ttl,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        setcookie(QC_PREV_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

// ---------------------------------------------------------------
// SIGNED COOKIE CHECK
// ---------------------------------------------------------------
function qc_cookie_valid() {
    if (qc_prev_active()) return true;
    if (empty($_COOKIE['_tsv'])) return false;
    return qc_verify_token(
        $_COOKIE['_tsv'],
        qc_ip(),
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    );
}

function qc_cookie_set($ttl = 1800) {
    $ip  = qc_ip();
    $ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $tok = qc_make_token($ip, $ua, $ttl);
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    setcookie('_tsv', $tok, [
        'expires'  => time() + $ttl,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    return $tok;
}

function qc_cookie_kill() {
    setcookie('_tsv', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// ---------------------------------------------------------------
// Per-session asset salt
// ---------------------------------------------------------------
function qc_asset_salt() {
    static $cached = null;
    if ($cached !== null) return $cached;
    if (!empty($_COOKIE['_as']) && preg_match('/^[a-f0-9]{8}$/', $_COOKIE['_as'])) {
        $cached = $_COOKIE['_as'];
        return $cached;
    }
    $cached = bin2hex(random_bytes(4));
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    if (!headers_sent()) {
        setcookie('_as', $cached, [
            'expires'  => time() + 1800,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
    $_COOKIE['_as'] = $cached;
    return $cached;
}

function asset($path) {
    $sep = (strpos($path, '?') !== false) ? '&' : '?';
    return $path . $sep . 's=' . qc_asset_salt();
}
