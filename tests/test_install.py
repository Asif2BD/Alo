"""Offline installer integration: serve fixtures through a test-only curl stub."""
import hashlib, os, shutil, subprocess, tempfile
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
PHP=os.environ.get('PHP_BINARY','php')
with tempfile.TemporaryDirectory(prefix='alo-bootstrap-') as tmp:
    tmp=Path(tmp); dest=tmp/'web directory'; dest.mkdir(); bins=tmp/'bin'; bins.mkdir()
    payload=tmp/'payload'; payload.write_bytes((ROOT/'alo.php').read_bytes())
    installer=tmp/'install.sh'; installer.write_text((ROOT/'site/install.sh.in').read_text().replace('@SHA@',hashlib.sha256(payload.read_bytes()).hexdigest()).replace('@SITE@','https://fixture.invalid').replace('@VERSION@','test'))
    curl=bins/'curl'; curl.write_text('#!/bin/sh\nwhile [ "$#" -gt 0 ]; do if [ "$1" = "-o" ]; then shift; cp "$TEST_PAYLOAD" "$1"; exit; fi; shift; done\nexit 2\n'); curl.chmod(0o755)
    env={k:v for k,v in os.environ.items() if not k.startswith('ALO_')};env.update(PHP_BINARY=PHP,TEST_PAYLOAD=str(payload),PATH=str(bins)+':'+env['PATH'])
    def install(*args): return subprocess.run(['sh',str(installer),'--dir',str(dest),*args],env=env,capture_output=True,text=True)
    assert install('--setup').returncode==0
    digest=(dest/'alo-hash.php').read_bytes(); code=(dest/'alo.php').read_bytes()
    assert install('--setup').returncode==0 and (dest/'alo-hash.php').read_bytes()==digest
    payload.write_text('tampered')
    assert install().returncode!=0 and (dest/'alo.php').read_bytes()==code
    assert (dest/'alo-hash.php').read_bytes()==digest
    assert not list(dest.glob('.alo-install.*'))
    (dest/'alo.php').unlink(); (dest/'alo.php').symlink_to(payload)
    assert install().returncode!=0 and payload.read_text()=='tampered'
print('Installer integrity, preservation, cleanup and symlink checks passed.')
