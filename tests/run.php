<?php
declare(strict_types=1);
define('ALO_TESTING', true);
require dirname(__DIR__) . '/alo.php';
$count = 0;
function check(bool $condition, string $message): void {
    global $count;
    ++$count;
    if (!$condition) { throw new RuntimeException($message); }
}
check(Alo\percent(10, 0) === null, 'Zero denominator');
check(Alo\percent(null, 100) === null, 'Missing value');
check(Alo\percent(125, 100) === 100.0, 'Bound percentage');
check(Alo\bytes(1024) === '1.0 KiB', 'Binary units');
check(Alo\bytes(null) === 'Unavailable', 'Unknown bytes');
$m = Alo\memoryInfo("MemTotal: 1000 kB\nMemAvailable: 250 kB\nMemFree: 10 kB\nSwapTotal: 100 kB\nSwapFree: 25 kB\n");
check($m['used_bytes'] === 768000.0 && $m['used_percent'] === 75.0, 'Use available memory not free');
check($m['swap_used_percent'] === 75.0, 'Swap percentage');
check(Alo\memoryInfo(null)['total_bytes'] === null, 'Missing procfs');
check(Alo\memoryInfo("MemTotal: 1000 kB\n")['used_bytes'] === null, 'Missing available memory');
$a = Alo\cpuTicks("cpu 100 20 30 400 20 10 10 10 50 5\n");
$b = Alo\cpuTicks("cpu 130 20 40 440 30 10 10 20 80 5\n");
check($a['total'] === 600.0, 'Exclude duplicate guest ticks');
check(Alo\cpuUsage($a, $b) === 50.0, 'CPU sample delta');
check(Alo\cpuUsage($a, $a) === null, 'No elapsed ticks');
check(Alo\cpuUsage($b, $a) === null, 'Counter reset');
check(Alo\cpuUsage(null, $b) === null, 'Missing CPU');
$n = Alo\networkInfo("eth0: 1000 2 3 4 0 0 0 0 2000 5 6 7 0 0 0 0\nlo: 50 0 0 0 0 0 0 0 50 0 0 0 0 0 0 0\n");
check(count($n) === 1 && $n[0]['sent_bytes'] === 2000.0, 'Counters and loopback');
check($n[0]['receive_drops'] === 4.0 && $n[0]['transmit_errors'] === 6.0, 'Network field mapping');
check(Alo\networkInfo(null) === [], 'Missing network');
check(Alo\supportStatus('8.5.1', '2026-09-08')['status'] === 'active', '8.5 support');
check(Alo\supportStatus('8.3.30', '2026-09-08')['status'] === 'security_only', '8.3 support');
check(Alo\supportStatus('8.3.30', '2028-01-01')['status'] === 'end_of_life', 'Expired branch');
check(Alo\supportStatus('9.0.0', '2029-01-01')['status'] === 'unknown', 'Unknown branch');
$t = str_repeat('a', 64); $h = hash('sha256', $t);
check(Alo\validToken($t, $h), 'Correct token');
check(!Alo\validToken(str_repeat('b', 64), $h), 'Wrong token');
check(!Alo\validToken('', ''), 'Unset credentials');
check(!Alo\validToken('short', hash('sha256', 'short')), 'Short token');
check(!Alo\validToken(str_repeat('a', 257), $h), 'Oversize token');
check(Alo\requestToken(['HTTP_AUTHORIZATION' => 'Bearer ' . $t]) === $t, 'Bearer');
check(Alo\requestToken(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('alo:' . $t)]) === $t, 'Basic');
check(Alo\requestToken(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('other:' . $t)]) === '', 'Wrong username');
check(Alo\requestToken(['HTTP_AUTHORIZATION' => ['invalid']]) === '', 'Malformed header');
check(Alo\requestToken(['HTTP_AUTHORIZATION' => 'Basic !!!']) === '', 'Malformed base64');
check(Alo\trustedHttps(['HTTPS' => 'on'], ''), 'Direct HTTPS');
check(!Alo\trustedHttps(['HTTP_X_FORWARDED_PROTO' => 'https', 'REMOTE_ADDR' => '192.0.2.1'], ''), 'Untrusted proxy');
check(Alo\trustedHttps(['HTTP_X_FORWARDED_PROTO' => 'https', 'REMOTE_ADDR' => '192.0.2.1'], '192.0.2.1'), 'Trusted proxy');
check(!Alo\trustedHttps(['HTTP_X_FORWARDED_PROTO' => 'https,http', 'REMOTE_ADDR' => '192.0.2.1'], '192.0.2.1'), 'Ambiguous protocol');
check(!Alo\trustedHttps(['HTTP_X_FORWARDED_PROTO' => 'https', 'REMOTE_ADDR' => '192.0.2.2'], '192.0.2.1'), 'Exact proxy match');
foreach (['LiteSpeed' => 'LiteSpeed / OpenLiteSpeed', 'OpenLiteSpeed' => 'OpenLiteSpeed', 'nginx/1.28' => 'Nginx', 'Apache/2.4' => 'Apache', 'Caddy' => 'Caddy', 'Microsoft-IIS/10' => 'Microsoft IIS'] as $input => $expected) {
    check(Alo\webServer(['SERVER_SOFTWARE' => $input], 'fpm-fcgi')['family'] === $expected, 'Web server family: ' . $expected);
}
check(Alo\webServer([], 'litespeed')['family'] === 'LiteSpeed / OpenLiteSpeed', 'LSAPI fallback');
check(Alo\webServer([], 'fpm-fcgi')['family'] === 'Unknown / not exposed', 'Do not invent proxy identity');
check(Alo\escape('<script>"&') === '&lt;script&gt;&quot;&amp;', 'Escape strings');
$r = Alo\collect();
check($r['schema_version'] === 1 && is_array($r['runtime']['extensions']), 'Report contract');
check(!isset($r['runtime']['settings']['error_log']), 'No log paths');
$r['disk']['used_percent'] = 95; $r['memory']['used_percent'] = 85;
$r['runtime']['settings']['allow_url_include'] = '1';
$f = Alo\insights($r);
check(count(array_filter($f, fn ($v) => $v['title'] === 'Disk pressure' && $v['severity'] === 'critical')) === 1, 'Disk insight');
check(count(array_filter($f, fn ($v) => $v['title'] === 'Host memory pressure' && $v['severity'] === 'warning')) === 1, 'RAM insight');
check(count(array_filter($f, fn ($v) => $v['title'] === 'Remote file inclusion is enabled')) === 1, 'Risky setting');
$r['cpu']['model'] = '<script>alert(1)</script>';
ob_start(); Alo\render($r, 'fixture-nonce'); $html = ob_get_clean();
check(!str_contains($html, '<script>alert(1)</script>') && str_contains($html, '&lt;script&gt;'), 'Render escapes collected data');
check(count(Alo\manifest()['mcp']['tools']) === 3, 'Agent manifest tools');
// --- deep telemetry parsers -------------------------------------------------
check(Alo\procPairs("anon 4096\nfile 8192\nbad line\n") === ['anon' => 4096.0, 'file' => 8192.0], 'Key/value pairs');
check(Alo\procPairs(null) === [], 'Missing pair file');
$mm = Alo\meminfoMap("MemTotal:  1000 kB\nHugePages_Total:  3\n");
check($mm['MemTotal'] === 1024000.0 && $mm['HugePages_Total'] === 3.0, 'kB scaled, page counts are not');

$sb = "cpu  100 10 50 800 20 5 5 10\ncpu0 50 5 25 400 10 2 2 5\ncpu1 50 5 25 400 10 3 3 5\nctxt 999\nintr 555 1 2\nprocs_running 3\nprocs_blocked 1\nbtime 1700000000\nprocesses 42\n";
$sa = "cpu  110 10 55 880 22 5 5 13\ncpu0 150 5 75 800 20 2 2 15\ncpu1 100 5 50 1200 20 3 3 5\nctxt 1999\nintr 999 1 2\nprocs_running 2\nprocs_blocked 0\nbtime 1700000000\nprocesses 84\n";
$bd = Alo\cpuBreakdown($sb, $sa);
check($bd['user_percent'] === 10.0 && $bd['idle_percent'] === 80.0 && $bd['system_percent'] === 5.0, 'CPU time breakdown shares');
check($bd['steal_percent'] === 3.0 && $bd['iowait_percent'] === 2.0, 'Steal and I/O wait are separate');
check(Alo\cpuBreakdown(null, $sa)['user_percent'] === null, 'Breakdown needs two samples');
$cores = Alo\cpuCoreUsage($sb, $sa);
check(count($cores) === 2 && $cores[0]['core'] === 0, 'Per-core list');
check($cores[0]['busy_percent'] > $cores[1]['busy_percent'], 'Per-core busy differs by core');
check(Alo\cpuCoreUsage(null, null) === [], 'Per-core without procfs');
$sched = Alo\schedulerInfo($sa);
check($sched['context_switches'] === 1999.0 && $sched['procs_running'] === 2.0, 'Scheduler counters');
check($sched['interrupts'] === 999.0, 'Interrupt total is the first column');
check(Alo\schedulerInfo(null)['boot_time'] === null, 'No boot time without procfs');

$psi = Alo\parsePressure("some avg10=1.50 avg60=0.80 avg300=0.20 total=12345\nfull avg10=0.10 avg60=0.05 avg300=0.00 total=99\n");
check($psi['some_avg10'] === 1.5 && $psi['full_total_us'] === 99.0, 'Pressure parsing');
check(Alo\parsePressure(null)['some_avg60'] === null, 'Pressure unavailable stays null');

$mounts = Alo\mountsInfo("/dev/vda1 / ext4 rw 0 0\nproc /proc proc rw 0 0\ntmpfs /run tmpfs rw 0 0\n/dev/vdb /data xfs rw 0 0\n",
    fn (string $path): array => $path === '/' ? [1000.0, 250.0] : [500.0, 400.0]);
check(count($mounts) === 2, 'Only real filesystems are listed');
check($mounts[0]['mount'] === '/' && $mounts[0]['used_percent'] === 75.0, 'Fullest filesystem first');
check(!array_key_exists('device', $mounts[0]), 'Device nodes are not collected');
check(Alo\mountsInfo(null) === [], 'No mounts without procfs');

$ds = Alo\diskstatsInfo("   8       0 vda 100 0 2000 0 50 0 400 0 0 1234 0\n   7       0 loop0 5 0 10 0 0 0 0 0 0 0 0\n");
check(count($ds) === 1 && $ds[0]['device'] === 'vda', 'Loop devices excluded');
check($ds[0]['read_bytes'] === 1024000.0, 'Sectors are 512 bytes');

$snmp = Alo\snmpInfo("Tcp: ActiveOpens PassiveOpens CurrEstab\nTcp: 10 20 30\nUdp: InDatagrams\nUdp: 40\n");
check($snmp['Tcp.CurrEstab'] === 30.0 && $snmp['Udp.InDatagrams'] === 40.0, 'SNMP counters keyed by protocol');
check(Alo\snmpInfo(null) === [], 'No SNMP without procfs');
$sock = Alo\sockstatInfo("sockets: used 200\nTCP: inuse 5 orphan 0 tw 3\n");
check($sock['sockets.used'] === 200.0 && $sock['TCP.tw'] === 3.0, 'Socket statistics');

// --- chart helpers ----------------------------------------------------------
check(str_contains(Alo\gauge(50.0, 'CPU', 'x'), 'stroke-dasharray="131.95 131.95"'), 'Half gauge is half the arc');
check(str_contains(Alo\gauge(null, 'CPU', 'x'), '>—<'), 'Missing gauge value shows an em dash');
check(str_contains(Alo\gauge(95.0, 'CPU', 'x'), 'arc crit'), 'High usage is toned critical');
check(!str_contains(Alo\gauge(10.0, '<b>', 'x'), '<b>'), 'Gauge labels are escaped');
$stack = Alo\stackBar([['A', 60.0, 'a'], ['B', 500.0, 'b']], 'aria');
check(str_contains($stack, 'width="60"') && str_contains($stack, 'width="40"'), 'Stack segments are clamped to 100%');
$chart = Alo\columnChart([['0', 100.0], ['1', null]], 'aria');
check(substr_count($chart, 'rect class="col ') === 2, 'One bar per core including unknowns');
check(str_contains($chart, 'viewBox="0 0 100 18"'), 'Column chart keeps a landscape viewBox');
check(Alo\columnChart([], 'aria') === '', 'No chart without cores');
check(str_contains(Alo\facts(['k' => null]), 'Unavailable'), 'Facts never fabricate a value');
check(str_contains(Alo\dataTable(['H'], []), 'No data exposed'), 'Empty tables say so');
check(Alo\duration(90061.0) === '1d 1h' && Alo\duration(null) === 'Unavailable', 'Duration formatting');
check(Alo\pct(null) === '—' && Alo\num(null) === 'Unavailable', 'Null formatting');

// --- new insight signals ----------------------------------------------------
$base = ['disk' => ['used_percent' => 10, 'mounts' => []], 'memory' => ['used_percent' => 10, 'swap_used_percent' => 0, 'detail' => []],
    'container' => ['memory_used_percent' => 10, 'cpu_throttled_percent' => 25.0, 'memory_events' => ['oom_kill' => 2.0]],
    'cpu' => ['logical_cores' => 4, 'load_5m' => 0.1, 'breakdown' => ['steal_percent' => 20.0]],
    'runtime' => ['settings' => ['display_errors' => '0', 'allow_url_include' => '0', 'expose_php' => '0', 'log_errors' => '1'],
        'support' => ['status' => 'active'], 'sapi' => 'fpm-fcgi'],
    'opcache' => ['enabled' => true, 'oom_restarts' => 3.0], 'kernel' => ['open_files_percent' => 95.0, 'cpu_temperature_c' => 20.0],
    'pressure' => ['cpu' => ['some_avg60' => 50.0], 'memory' => ['some_avg60' => 0.0], 'io' => ['some_avg60' => 0.0]],
    'sockets' => ['retransmit_percent' => 5.0], 'paging' => ['swap_out' => 0.0]];
$titles = array_column(Alo\insights($base), 'title');
foreach (['Container CPU throttling recorded', 'The cgroup has killed processes for memory',
    'CPU pressure is stalling work', 'The hypervisor is taking CPU time',
    'Open file descriptors are near the kernel limit', 'TCP retransmissions recorded',
    'OPcache has restarted out of memory'] as $expected) {
    check(in_array($expected, $titles, true), "Insight: $expected");
}
check(Alo\insights($base + ['x' => 1]) !== [], 'Insights tolerate partial reports');
$partial = Alo\withDefaults(['memory' => [], 'runtime' => []]);
check($partial['kernel']['open_files'] === null && $partial['sockets']['tcp_in_use'] === null, 'Defaults fill missing families');

// --- the CPU bar must account for every /proc/stat field ---------------------
$niced = "cpu  0 0 0 0 0 0 0 0\n";
$after = "cpu  1 90 4 1 1 1 1 1\n";
$nb = Alo\cpuBreakdown($niced, $after);
check(abs(array_sum(array_values($nb)) - 100.0) < 0.5, 'Breakdown fields sum to 100%');
check($nb['nice_percent'] === 90.0, 'Niced time is measured');
$rep = Alo\withDefaults(['cpu' => ['busy_percent' => 99.0, 'breakdown' => $nb, 'per_core' => [], 'load_1m' => 0, 'load_5m' => 0, 'load_15m' => 0, 'logical_cores' => 1, 'sample_ms' => 100, 'model' => 'x'],
    'memory' => ['total_bytes' => 100.0, 'used_bytes' => 50.0, 'used_percent' => 50.0, 'available_bytes' => 50.0, 'swap_total_bytes' => 0, 'swap_used_bytes' => 0, 'swap_used_percent' => null],
    'disk' => ['used_percent' => 10.0, 'free_bytes' => 1.0, 'total_bytes' => 2.0, 'used_bytes' => 1.0],
    'runtime' => ['php_version' => '8.4.0', 'sapi' => 'cli', 'os_family' => 'Linux', 'architecture_bits' => 64,
        'settings' => [], 'extensions' => [], 'database_drivers' => [], 'process_memory_bytes' => 1, 'process_peak_bytes' => 1,
        'support' => Alo\supportStatus('8.4.0', '2026-09-09')],
    'web_server' => ['family' => 'Nginx', 'php_handler' => 'fpm-fcgi'], 'network' => [], 'opcache' => ['enabled' => false],
    'uptime_seconds' => 10.0, 'collected_at' => 'now', 'collection_ms' => 1.0, 'insights' => []]);
ob_start(); Alo\render($rep, 'n'); $out = ob_get_clean();
preg_match('/<svg class="stack".*?<\/svg>/s', $out, $firstBar);
preg_match_all('/width="([\d.]+)"/', $firstBar[0] ?? '', $seg);
check($seg[1] !== [] && abs(array_sum(array_map('floatval', $seg[1])) - 100.0) < 0.5, 'Rendered CPU bar covers the full width');
check(str_contains($out, 'Nice</li>') || str_contains($out, '>Nice '), 'Nice appears in the legend');
check(str_contains($out, 'no kernel ceiling'), 'Unbounded descriptor limit is described, not printed as a sentinel');

check(Alo\ratio(1027, 1000) === 102.7, 'Overcommit is reported above 100%');
check(Alo\percent(1027, 1000) === 100.0, 'Gauges still clamp to 100%');
check(Alo\ratio(1, 0) === null && Alo\ratio(null, 5) === null, 'Ratio guards missing data');

$single = Alo\columnChart([['0', 50.0]], 'aria');
preg_match('/rect class="col-track" x="([\d.]+)" y="1" width="([\d.]+)"/', $single, $bar);
check((float) $bar[2] <= 9.0, 'A single core does not draw a full-width slab');
check(abs((float) $bar[1] + (float) $bar[2] / 2 - 50.0) < 0.01, 'A single bar is centred');

// --- the published contract must match the code -----------------------------
// llms.txt and the OpenAPI description are what an agent reads before it ever
// calls Alo. They drifted once already; this fails the build if they do again.
$source = file_get_contents(dirname(__DIR__) . '/alo.php');
preg_match("/!in_array\(\\\$_GET\['format'\], \[([^\]]+)\]/", $source, $accepted);
$formats = array_map(static fn (string $f): string => trim($f, " '"), explode(',', $accepted[1]));
sort($formats);
check($formats === ['html', 'json', 'manifest', 'mcp', 'metrics'], 'Accepted formats are what we think they are');

$builder = file_get_contents(dirname(__DIR__) . '/site/build.sh');
preg_match('/"enum": \[([^\]]*"metrics"[^\]]*)\]/', $builder, $documented);
$published = array_map(static fn (string $f): string => trim($f, ' "'), explode(',', $documented[1] ?? ''));
$published[] = 'mcp';   // documented as its own OpenAPI path
sort($published);
check($published === $formats, 'OpenAPI documents every format the code accepts');

$llms = file_get_contents(dirname(__DIR__) . '/llms.txt');
foreach ($formats as $format) {
    check(str_contains($llms, 'format=' . $format), "llms.txt lists format=$format");
}
check(str_contains($llms, 'sample=') && str_contains($llms, 'fields='), 'llms.txt lists the query parameters');
check(str_contains($builder, '"name": "sample"') && str_contains($builder, '"name": "fields"'), 'OpenAPI documents the query parameters');

// Lifetime evidence must not masquerade as an active incident.
$observations = Alo\insights($base);
foreach ($observations as $item) {
    check(isset($item['state'], $item['scope'], $item['window'], $item['evidence_family']), 'Observation has interpretation metadata');
    if (in_array($item['title'], ['Container CPU throttling recorded', 'The cgroup has killed processes for memory', 'TCP retransmissions recorded', 'OPcache has restarted out of memory'], true)) {
        check($item['state'] === 'historical' && $item['severity'] === 'info', 'Lifetime event is historical evidence, not an active alert');
    }
}
echo "$count checks passed.\n";
