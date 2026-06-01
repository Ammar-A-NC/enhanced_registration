#!/usr/bin/env sh
set -eu

echo "PHP syntax check"
find . -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null

echo "Dangerous function smell check"
if grep -RInE '(^|[^[:alnum:]_])(eval|shell_exec|system|passthru|exec|base64_decode|unserialize)[[:space:]]*\(' lib templates appinfo; then
  echo "Potential dangerous function found"
  exit 1
fi

echo "Hardcoded public index.php URLs"
grep -RIn 'index.php/apps\|index.php/settings' lib templates js appinfo || true

echo "OK"
