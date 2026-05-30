import json
import os
import re
import subprocess
from http.server import BaseHTTPRequestHandler, HTTPServer

BRIDGE_SECRET = os.environ.get("BRIDGE_SECRET", "")
LDAP_URL = os.environ.get("LDAP_URL", "ldap://lldap:3890")
LDAP_BIND_DN = os.environ.get("LDAP_BIND_DN", "uid=admin,ou=people,dc=example,dc=com")
LDAP_BIND_PASSWORD = os.environ.get("LDAP_BIND_PASSWORD", "")
LDAP_USER_BASE_DN = os.environ.get("LDAP_USER_BASE_DN", "ou=people,dc=example,dc=com")
BRIDGE_PORT = int(os.environ.get("BRIDGE_PORT", "18080"))

USERNAME_RE = re.compile(r"^[A-Za-z][A-Za-z0-9._-]{2,63}$")


def escape_ldap_dn_value(value: str) -> str:
    result = []

    for ch in value:
        if ch == "\\00":
            result.append("\\00")
        elif ch in ['\\', ',', '+', '"', '<', '>', ';', '=']:
            result.append("\\" + ch)
        else:
            result.append(ch)

    escaped = "".join(result)

    if escaped.startswith("#"):
        escaped = "\\#" + escaped[1:]

    if escaped.startswith(" "):
        escaped = "\\ " + escaped[1:]

    if escaped.endswith(" "):
        escaped = escaped[:-1] + "\\ "

    return escaped


class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == "/health":
            self.send_response(200)
            self.end_headers()
            self.wfile.write(b"ok")
            return

        self.send_response(405)
        self.end_headers()
        self.wfile.write(b"use POST")

    def do_POST(self):
        if self.path not in ["/", "/set-password"]:
            self.send_response(404)
            self.end_headers()
            self.wfile.write(b"not found")
            return

        length = int(self.headers.get("Content-Length", "0"))
        body = self.rfile.read(length)

        try:
            data = json.loads(body.decode("utf-8"))
        except Exception:
            self.send_response(400)
            self.end_headers()
            self.wfile.write(b"bad json")
            return

        if not BRIDGE_SECRET or data.get("secret") != BRIDGE_SECRET:
            self.send_response(403)
            self.end_headers()
            self.wfile.write(b"forbidden")
            return

        username = str(data.get("username", "")).strip()
        password = str(data.get("password", ""))

        if not username or not password:
            self.send_response(400)
            self.end_headers()
            self.wfile.write(b"missing username/password")
            return

        if not USERNAME_RE.match(username):
            self.send_response(400)
            self.end_headers()
            self.wfile.write(b"invalid username")
            return

        if not LDAP_BIND_PASSWORD:
            self.send_response(500)
            self.end_headers()
            self.wfile.write(b"ldap bind password missing")
            return

        user_dn = "uid=" + escape_ldap_dn_value(username) + "," + LDAP_USER_BASE_DN

        try:
            proc = subprocess.run(
                [
                    "ldappasswd",
                    "-x",
                    "-H", LDAP_URL,
                    "-D", LDAP_BIND_DN,
                    "-w", LDAP_BIND_PASSWORD,
                    "-s", password,
                    user_dn,
                ],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                timeout=20,
            )

            if proc.returncode != 0:
                self.send_response(500)
                self.end_headers()
                msg = proc.stderr or proc.stdout or b"ldappasswd failed"
                self.wfile.write(msg[:1000])
                return

            self.send_response(200)
            self.end_headers()
            self.wfile.write(b"ok")
        except Exception as e:
            self.send_response(500)
            self.end_headers()
            self.wfile.write(str(e).encode("utf-8")[:1000])

    def log_message(self, fmt, *args):
        print("%s - %s" % (self.address_string(), fmt % args), flush=True)


HTTPServer(("0.0.0.0", BRIDGE_PORT), Handler).serve_forever()
