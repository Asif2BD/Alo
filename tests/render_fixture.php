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
// Deep telemetry families, so the sample shows the same shape a Linux host reports.
$data['cpu'] += ['physical_packages' => 1, 'mhz' => 2445.4, 'cache' => '32768 KB', 'load_per_core' => 0.16,
    'breakdown' => ['user_percent' => 9.4, 'nice_percent' => 0.1, 'system_percent' => 3.6, 'idle_percent' => 84.2,
        'iowait_percent' => 1.4, 'irq_percent' => 0.2, 'softirq_percent' => 0.6, 'steal_percent' => 0.5],
    'per_core' => [['core' => 0, 'busy_percent' => 22.4], ['core' => 1, 'busy_percent' => 11.8],
        ['core' => 2, 'busy_percent' => 9.2], ['core' => 3, 'busy_percent' => 31.6],
        ['core' => 4, 'busy_percent' => 7.4], ['core' => 5, 'busy_percent' => 12.1],
        ['core' => 6, 'busy_percent' => 8.8], ['core' => 7, 'busy_percent' => 13.5]],
    'scheduler' => ['context_switches' => 4821993421.0, 'interrupts' => 2914772210.0, 'softirqs' => 1882014422.0,
        'forks_since_boot' => 8412330.0, 'procs_running' => 2.0, 'procs_blocked' => 0.0,
        'boot_time' => '2026-08-05T05:00:00+00:00']];
$data['memory']['detail'] = ['free_bytes' => 1.1 * $gib, 'buffers_bytes' => 0.4 * $gib,
    'cached_bytes' => 4.74 * $gib, 'shmem_bytes' => 0.22 * $gib, 'dirty_bytes' => 12 * 1024 ** 2,
    'writeback_bytes' => 0, 'anon_bytes' => 8.9 * $gib, 'mapped_bytes' => 0.9 * $gib,
    'slab_bytes' => 0.62 * $gib, 'slab_reclaimable_bytes' => 0.48 * $gib,
    'page_tables_bytes' => 0.07 * $gib, 'committed_bytes' => 13.4 * $gib,
    'commit_limit_bytes' => 18 * $gib, 'commit_used_percent' => 74.4,
    'active_bytes' => 7.8 * $gib, 'inactive_bytes' => 5.1 * $gib,
    'hugepages_total' => 0, 'hugepages_free' => 0];
$data['disk'] += ['scope' => 'Filesystem containing alo.php.',
    'mounts' => [
        ['mount' => '/', 'filesystem' => 'ext4', 'total_bytes' => 200 * $gib, 'free_bytes' => 36 * $gib, 'used_bytes' => 164 * $gib, 'used_percent' => 82.0],
        ['mount' => '/var/lib/docker', 'filesystem' => 'overlay', 'total_bytes' => 200 * $gib, 'free_bytes' => 36 * $gib, 'used_bytes' => 164 * $gib, 'used_percent' => 82.0],
        ['mount' => '/boot', 'filesystem' => 'ext4', 'total_bytes' => 0.94 * $gib, 'free_bytes' => 0.79 * $gib, 'used_bytes' => 0.15 * $gib, 'used_percent' => 15.4]],
    'devices' => [
        ['device' => 'vda', 'reads' => 1842200.0, 'read_bytes' => 88.4 * $gib, 'writes' => 9128400.0, 'written_bytes' => 421.2 * $gib, 'io_in_progress' => 0.0, 'io_active_ms' => 4820000.0],
        ['device' => 'vdb', 'reads' => 24100.0, 'read_bytes' => 1.2 * $gib, 'writes' => 88200.0, 'written_bytes' => 6.4 * $gib, 'io_in_progress' => 0.0, 'io_active_ms' => 91000.0]]];
$data['pressure'] = ['scope' => 'Pressure Stall Information.',
    'cpu' => ['some_avg10' => 2.14, 'some_avg60' => 1.82, 'some_avg300' => 1.44, 'some_total_us' => 91882110.0,
        'full_avg10' => null, 'full_avg60' => null, 'full_avg300' => null, 'full_total_us' => null],
    'memory' => ['some_avg10' => 0.0, 'some_avg60' => 0.02, 'some_avg300' => 0.11, 'some_total_us' => 4218800.0,
        'full_avg10' => 0.0, 'full_avg60' => 0.0, 'full_avg300' => 0.04, 'full_total_us' => 1180400.0],
    'io' => ['some_avg10' => 0.44, 'some_avg60' => 0.81, 'some_avg300' => 1.02, 'some_total_us' => 55210400.0,
        'full_avg10' => 0.21, 'full_avg60' => 0.40, 'full_avg300' => 0.55, 'full_total_us' => 28110900.0]];
$data['paging'] = ['page_faults' => 88421330.0, 'major_page_faults' => 12840.0, 'swap_in' => 0.0,
    'swap_out' => 0.0, 'oom_kills' => 0.0, 'direct_reclaim' => 0.0];
$data['container'] += ['scope' => 'Visible cgroup v2 root.', 'note' => 'Null limits mean unlimited or unavailable.',
    'version' => 'v2', 'memory_high_bytes' => 3.6 * $gib, 'memory_peak_bytes' => 3.1 * $gib,
    'memory_swap_used_bytes' => 0, 'memory_anon_bytes' => 1.8 * $gib, 'memory_file_bytes' => 0.42 * $gib,
    'memory_slab_bytes' => 0.08 * $gib,
    'memory_events' => ['low' => 0.0, 'high' => 0.0, 'max' => 0.0, 'oom' => 0.0, 'oom_kill' => 0.0],
    'cpu_usage_usec' => 1884210000.0, 'cpu_periods' => 998220.0, 'cpu_throttled_periods' => 1420.0,
    'cpu_throttled_usec' => 8820000.0, 'cpu_throttled_percent' => 0.14,
    'pids_current' => 148.0, 'pids_max' => 4096.0];
$data['kernel'] = ['distribution' => 'Ubuntu 24.04.4 LTS', 'kernel_version' => '6.8.0-51-generic',
    'open_files' => 14208.0, 'open_files_max' => null, 'open_files_limited' => false, 'open_files_percent' => null,
    'cpu_temperature_c' => 48.2, 'cpu_governor' => 'performance', 'entropy_available' => 256.0,
    'sysctl' => ['vm.swappiness' => '60', 'vm.overcommit_memory' => '0', 'vm.dirty_ratio' => '20',
        'kernel.pid_max' => '4194304', 'net.core.somaxconn' => '4096',
        'net.ipv4.tcp_max_syn_backlog' => '1024', 'net.ipv4.ip_local_port_range' => '32768\t60999',
        'fs.nr_open' => '1048576'],
    'note' => 'Kernel build host, hostname, and addresses are deliberately not collected.'];
$data['network'][0] += ['speed_mbit' => 10000.0, 'mtu' => 1500.0, 'state' => 'up'];
$data['sockets'] = ['tcp_established' => 184.0, 'tcp_active_opens' => 9214400.0, 'tcp_passive_opens' => 12844100.0,
    'tcp_retransmitted_segments' => 18420.0, 'tcp_segments_in' => 884221000.0, 'tcp_segments_out' => 902114000.0,
    'tcp_errors_in' => 12.0, 'tcp_resets_out' => 44210.0, 'udp_datagrams_in' => 1882100.0,
    'udp_receive_errors' => 0.0, 'sockets_used' => 412.0, 'tcp_in_use' => 188.0, 'tcp_time_wait' => 96.0,
    'tcp_orphan' => 0.0, 'retransmit_percent' => 0.0,
    'note' => 'Cumulative kernel counters since boot.'];
$data['opcache'] += ['wasted_percent' => 0.24, 'cached_keys' => 1248, 'max_cached_keys' => 16229,
    'hits' => 48221900.0, 'misses' => 86400.0, 'oom_restarts' => 0, 'hash_restarts' => 0, 'manual_restarts' => 0,
    'interned_used_bytes' => 3.8 * 1024 ** 2, 'interned_free_bytes' => 4.2 * 1024 ** 2, 'interned_strings' => 42188,
    'jit_enabled' => true, 'jit_buffer_bytes' => 64 * 1024 ** 2, 'jit_buffer_free_bytes' => 61.4 * 1024 ** 2,
    'realpath_cache_bytes' => 1.1 * 1024 ** 2];
$data['runtime'] += ['zend_version' => '4.5.0', 'thread_safe' => false, 'debug_build' => false,
    'extension_versions' => []];

$data['insights'] = Alo\insights($data);
ob_start();
Alo\render($data, 'sample-fixture-only');
$html = ob_get_clean();
echo str_replace('Your server, a little clearer', 'Illustrative sample · not a live server', $html);
