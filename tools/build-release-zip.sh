#!/usr/bin/env bash
set -euo pipefail

repo_root="$(git rev-parse --show-toplevel)"
cd "$repo_root"

read_app_info() {
  python3 - "$1" <<'PYINFO'
import re
import sys
from pathlib import Path

field = sys.argv[1]
text = Path("appinfo/info.xml").read_text(encoding="utf-8")

match = re.search(r"<" + re.escape(field) + r">\s*([^<]+?)\s*</" + re.escape(field) + r">", text)

if not match:
    raise SystemExit(1)

print(match.group(1))
PYINFO
}

app_id="$(read_app_info id)"
version="$(read_app_info version)"

if [ -z "$app_id" ] || [ -z "$version" ]; then
  echo "Could not read app id/version from appinfo/info.xml" >&2
  exit 1
fi

if [ "${ALLOW_DIRTY:-0}" != "1" ] && [ -n "$(git status --porcelain)" ]; then
  echo "Working tree is dirty. Commit first, or run with ALLOW_DIRTY=1." >&2
  git status --short >&2
  exit 1
fi

mkdir -p dist

output="dist/${app_id}-v${version}.zip"
checksum="${output}.sha256"

rm -f "$output" "$checksum"

git archive \
  --format=zip \
  --prefix="${app_id}/" \
  -o "$output" \
  HEAD

python3 - "$output" "$app_id" "$version" <<'PYZIP'
import re
import sys
import zipfile

zip_path, app_id, expected_version = sys.argv[1:4]

with zipfile.ZipFile(zip_path) as zf:
    names = zf.namelist()

    if not names:
        raise SystemExit("Release ZIP is empty.")

    bad_top_level = [name for name in names if not name.startswith(app_id + "/")]
    if bad_top_level:
        raise SystemExit("ZIP contains entries outside expected top-level folder: " + ", ".join(bad_top_level[:10]))

    required = app_id + "/appinfo/info.xml"
    if required not in names:
        raise SystemExit("ZIP does not contain " + required)

    forbidden_parts = ["/.git/", "/dist/", "/.github/", "/tools/"]
    forbidden = [name for name in names if any(part in name for part in forbidden_parts)]
    if forbidden:
        raise SystemExit("ZIP contains development-only paths: " + ", ".join(forbidden[:10]))

    info_xml = zf.read(required).decode("utf-8")
    match = re.search(r"<version>([^<]+)</version>", info_xml)
    if not match:
        raise SystemExit("Could not read version from zipped appinfo/info.xml")

    actual_version = match.group(1)
    if actual_version != expected_version:
        raise SystemExit(f"ZIP version mismatch: expected {expected_version}, got {actual_version}")

print(f"OK: {zip_path} contains {app_id}/ with version {expected_version}")
PYZIP

sha256sum "$output" | tee "$checksum"

ls -lh "$output" "$checksum"
