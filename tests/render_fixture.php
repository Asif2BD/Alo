<?php
declare(strict_types=1);
// CLI only: this is screenshot sample data, never a public demo route.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('ALO_TESTING', true);
require dirname(__DIR__) . '/alo.php';
$gib = 1024 ** 3;
$data = [
    'schema_version' => 1, 'alo_version' => Alo\VERSION,
    'collected_at' => '2026-09-08T12:00:00+00:00', 'collection_ms' => 104.2,
    'scope' => 'Illustrative sample data. Not a live server.',
    'web_server' => ['family' => 'LiteSpeed / OpenLiteSpeed', 'php_handler' => 'litespeed'],
    'runtime' => [
        'php_version' => '8.5.0', 'sapi' => 'litespeed', 'os_family' => 'Linux', 'architecture_bits' => 64,
        'process_memory_bytes' => 2 * 1024 ** 2, 'process_peak_bytes' => 2 * 1024 ** 2,
        'support' => Alo\supportStatus('8.5.0', '2026-09-08'),
        'settings' => ['memory_limit' => '256M', 'max_execution_time' => '30', 'max_input_time' => '60',
            'max_input_vars' => '1000', 'post_max_size' => '64M', 'upload_max_filesize' => '64M',
            'date.timezone' => 'UTC', 'display_errors' => '0', 'log_errors' => '1', 'expose_php' => '1',
            'allow_url_include' => '0', 'allow_url_fopen' => '1', 'session.cookie_secure' => '1',
            'session.cookie_httponly' => '1', 'session.cookie_samesite' => 'Lax', 'session.use_strict_mode' => '1'],
        'extensions' => ['Core', 'ctype', 'curl', 'date', 'dom', 'fileinfo', 'filter', 'gd', 'hash', 'iconv',
            'intl', 'json', 'libxml', 'mbstring', 'mysqli', 'openssl', 'pcre', 'PDO', 'pdo_mysql', 'pdo_sqlite',
            'Phar', 'random', 'readline', 'Reflection', 'session', 'SimpleXML', 'sodium', 'SPL', 'sqlite3',
            'standard', 'tokenizer', 'xml', 'xmlreader', 'xmlwriter', 'Zend OPcache', 'zip', 'zlib'],
        'database_drivers' => ['mysql', 'sqlite'],
    ],
    'cpu' => ['model' => 'AMD EPYC · sample virtual CPU', 'logical_cores' => 8, 'busy_percent' => 14.6,
        'sample_ms' => 100, 'load_1m' => 1.24, 'load_5m' => 1.08, 'load_15m' => 0.92],
    'memory' => ['total_bytes' => 16 * $gib, 'used_bytes' => 9.76 * $gib, 'available_bytes' => 6.24 * $gib,
        'used_percent' => 61.0, 'swap_total_bytes' => 2 * $gib, 'swap_used_bytes' => 0, 'swap_used_percent' => 0],
    'disk' => ['total_bytes' => 200 * $gib, 'used_bytes' => 164 * $gib, 'free_bytes' => 36 * $gib, 'used_percent' => 82.0],
    'uptime_seconds' => 34 * 86400 + 7 * 3600,
    'container' => ['memory_used_bytes' => 2.3 * $gib, 'memory_limit_bytes' => 4 * $gib,
        'memory_used_percent' => 57.5, 'cpu_quota_cores' => 4],
    'network' => [['interface' => 'eth0', 'received_bytes' => 128.4 * $gib, 'sent_bytes' => 42.8 * $gib,
        'receive_errors' => 0, 'transmit_errors' => 0, 'receive_drops' => 0, 'transmit_drops' => 0]],
    'opcache' => ['available' => true, 'enabled' => true, 'used_bytes' => 82 * 1024 ** 2,
        'free_bytes' => 45.8 * 1024 ** 2, 'wasted_bytes' => .2 * 1024 ** 2,
        'hit_rate_percent' => 99.82, 'cached_scripts' => 1248, 'restart_pending' => false],
];
$data['insights'] = Alo\insights($data);
ob_start();
Alo\render($data, 'sample-fixture-only');
$html = ob_get_clean();
echo str_replace('Your server, a little clearer', 'Illustrative sample · not a live server', $html);
