"""Real HTTP and MCP regressions; Python standard library plus PHP CLI only."""
import base64
import contextlib
import hashlib
import http.client
import json
import os
from pathlib import Path
import socket
import subprocess
import time
ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('PHP_BINARY', 'php')
TOKEN = 'test-only-' + 'a' * 64
HASH = hashlib.sha256(TOKEN.encode()).hexdigest()
AUTH = {'Authorization': 'Bearer ' + TOKEN}
checks = 0

def check(condition, message):
    global checks
    checks += 1
    if not condition:
        raise AssertionError(message)

@contextlib.contextmanager
def server(settings):
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        port = sock.getsockname()[1]
    env = {k: v for k, v in os.environ.items() if not k.startswith('ALO_')}
    env.update(settings)
    env['ALO_TEST_SECRET'] = 'never-export-this-private-value'
    proc = subprocess.Popen([PHP, '-d', 'display_errors=1', '-S', f'127.0.0.1:{port}', '-t', str(ROOT)],
                            env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    try:
        for _ in range(100):
            if proc.poll() is not None:
                raise RuntimeError('PHP server exited')
            try:
                with socket.create_connection(('127.0.0.1', port), timeout=.1):
                    break
            except OSError:
                time.sleep(.05)
        else:
            raise RuntimeError('PHP server did not start')
        yield port
    finally:
        proc.terminate()
        proc.wait(timeout=5)

def request(port, path='/alo.php', headers=None, method='GET', body=None):
    conn = http.client.HTTPConnection('127.0.0.1', port, timeout=5)
    conn.request(method, path, body=body, headers=headers or {})
    r = conn.getresponse()
    result = r.status, {k.lower(): v for k, v in r.getheaders()}, r.read().decode()
    conn.close()
    return result

with server({'ALO_ALLOW_LOCAL_HTTP': '1'}) as port:
    s, h, b = request(port, headers=AUTH)
    check(s == 503 and 'locked' in b, 'Fail closed without config')
    check('no-store' in h['cache-control'], 'Errors never cached')
with server({'ALO_TOKEN_HASH': HASH}) as port:
    check(request(port, headers=AUTH)[0] == 403, 'HTTP denied')
    check(request(port, headers={**AUTH, 'X-Forwarded-Proto': 'https'})[0] == 403, 'Forged proxy denied')
with server({'ALO_TOKEN_HASH': HASH, 'ALO_ALLOW_LOCAL_HTTP': '1'}) as port:
    s, h, b = request(port)
    check(s == 401 and 'www-authenticate' in h, 'HTML auth')
    for fmt in ['json', 'manifest', 'mcp']:
        check(request(port, '/alo.php?format=' + fmt)[0] == 401, fmt + ' auth')
    check(request(port, headers={'Authorization': 'Bearer ' + 'z' * 64})[0] == 401, 'Wrong token')
    check(request(port, '/alo.php?token=' + TOKEN)[0] == 401, 'No URL auth')
    check(request(port, headers={'Authorization': 'Basic !!!'})[0] == 401, 'Invalid Basic')
    check(request(port, headers=AUTH, method='POST')[0] == 405, 'No legacy POST')
    for q in ['act=phpinfo', 'act=rt&callback=alert', 'format[]=json', 'format=xml', 'speed=0', 'path=/etc/passwd']:
        check(request(port, '/alo.php?' + q, AUTH)[0] == 400, 'Rejected: ' + q)
    s, h, b = request(port, '/alo.php?format=json', AUTH)
    check(s == 200 and h['content-type'].startswith('application/json'), 'JSON response')
    r = json.loads(b)
    check(r['schema_version'] == 1 and r['runtime']['php_version'], 'JSON schema')
    check('never-export-this-private-value' not in b and TOKEN not in b and HASH not in b, 'No secret leak')
    check(str(ROOT) not in b and 'HTTP_AUTHORIZATION' not in b, 'No path/request dump')
    check(any(i['title'] == 'PHP errors may be exposed' for i in r['insights']), 'Preserve original display_errors diagnosis')
    basic = {'Authorization': 'Basic ' + base64.b64encode(('alo:' + TOKEN).encode()).decode()}
    s, h, html = request(port, headers=basic)
    check(s == 200 and 'A little light' in html, 'Basic browser view')
    check('nonce-' in h['content-security-policy'] and "frame-ancestors 'none'" in h['content-security-policy'], 'Nonce CSP')
    check('no-store' in h['cache-control'] and 'access-control-allow-origin' not in h, 'Private response')
    check(h['x-content-type-options'] == 'nosniff' and h['referrer-policy'] == 'no-referrer', 'Headers')
    check('x-powered-by' not in h, 'Version header removed')
    check('<script src=' not in html and '<link ' not in html, 'No external assets')
    check(json.loads(request(port, '/alo.php?format=manifest', AUTH)[2])['mcp']['tools'][0] == 'alo_snapshot', 'Manifest')
    mh = {**AUTH, 'Content-Type': 'application/json', 'Accept': 'application/json, text/event-stream', 'MCP-Protocol-Version': '2025-11-25'}
    def rpc(payload, headers=None):
        return request(port, '/alo.php?format=mcp', mh if headers is None else headers, 'POST', json.dumps(payload))
    init = {'jsonrpc': '2.0', 'id': 1, 'method': 'initialize', 'params': {'protocolVersion': '2025-11-25', 'capabilities': {}, 'clientInfo': {'name': 'test', 'version': '1'}}}
    s, h, b = rpc(init)
    check(s == 200 and json.loads(b)['result']['protocolVersion'] == '2025-11-25', 'MCP initialize')
    check(rpc({'jsonrpc': '2.0', 'method': 'notifications/initialized'})[0] == 202, 'Notification')
    s, h, b = rpc({'jsonrpc': '2.0', 'id': 2, 'method': 'tools/list'})
    check(len(json.loads(b)['result']['tools']) == 3, 'MCP discovery')
    for tool in ['alo_snapshot', 'alo_insights', 'alo_capabilities']:
        s, h, b = rpc({'jsonrpc': '2.0', 'id': 3, 'method': 'tools/call', 'params': {'name': tool, 'arguments': {}}})
        result = json.loads(b)['result']
        check(s == 200 and not result['isError'] and isinstance(result['structuredContent'], dict), 'MCP tool ' + tool)
    check(json.loads(rpc({'jsonrpc': '2.0', 'id': 4, 'method': 'ping'})[2])['result'] == {}, 'Ping object')
    check(json.loads(rpc({'jsonrpc': '2.0', 'id': 5, 'method': 'tools/call', 'params': {'name': 'alo_snapshot', 'arguments': {'path': '/etc/passwd'}}})[2])['error']['code'] == -32602, 'No arbitrary MCP arguments')
    check(json.loads(rpc({'jsonrpc': '2.0', 'id': 6, 'method': 'shell/exec'})[2])['error']['code'] == -32601, 'Unknown method')
    check(rpc(init, {**mh, 'Origin': 'https://evil.example'})[0] == 403, 'Origin rejected')
    check(rpc(init, {**mh, 'MCP-Protocol-Version': 'invalid'})[0] == 400, 'Bad protocol')
    check(rpc(init, {**mh, 'Content-Type': 'text/plain'})[0] == 415, 'MCP content type')
    check(rpc(init, {**mh, 'Accept': 'text/html'})[0] == 406, 'MCP accept')
    check(request(port, '/alo.php?format=mcp', AUTH)[0] == 405, 'No SSE')
    check(request(port, '/alo.php?format=mcp', mh, 'POST', '{')[0] == 400, 'Bad JSON')
    check(rpc([init])[0] == 400, 'No batching')
    check(rpc({'jsonrpc': '2.0', 'id': None, 'method': 'ping'})[0] == 400, 'Null request ID rejected')
    check(request(port, '/alo.php?format=mcp', mh, 'POST', ' ' * 65537)[0] == 413, 'Bounded MCP input')
    for version in ['2025-06-18', '2025-03-26']:
        payload = {**init, 'params': {**init['params'], 'protocolVersion': version}}
        check(json.loads(rpc(payload, {**mh, 'MCP-Protocol-Version': version})[2])['result']['protocolVersion'] == version, 'Negotiate ' + version)
with server({'ALO_TOKEN_HASH': HASH, 'ALO_TRUSTED_PROXIES': '127.0.0.1'}) as port:
    s, h, _ = request(port, headers={**AUTH, 'X-Forwarded-Proto': 'https'})
    check(s == 200 and 'strict-transport-security' in h, 'Trusted TLS proxy')
    check(request(port, headers={**AUTH, 'X-Forwarded-Proto': 'https,http'})[0] == 403, 'Ambiguous TLS proxy')
print(f'{checks} HTTP checks passed.')
