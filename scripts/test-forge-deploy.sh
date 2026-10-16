#!/usr/bin/env bash
set -euo pipefail

script_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
test_dir=$(mktemp -d)
trap 'rm -rf "$test_dir"' EXIT
mkdir -p "$test_dir/bin" "$test_dir/site"

cat > "$test_dir/bin/php8.3" <<'STUB'
#!/usr/bin/env bash
printf '%s\n' "$*" >> "$DEPLOY_TEST_LOG"
STUB
cat > "$test_dir/bin/composer" <<'STUB'
#!/usr/bin/env bash
printf 'composer %s\n' "$*" >> "$DEPLOY_TEST_LOG"
STUB
cat > "$test_dir/bin/git" <<'STUB'
#!/usr/bin/env bash
if [[ "$1" == rev-parse ]]; then
    printf '%040d\n' 1
fi
STUB
cat > "$test_dir/bin/curl" <<'STUB'
#!/usr/bin/env bash
exit 0
STUB
chmod +x "$test_dir/bin/"*
export PATH="$test_dir/bin:$PATH"
export FORGE_PHP=php8.3
export FORGE_SITE_PATH="$test_dir/site"
export DEPLOY_TEST_LOG="$test_dir/commands.log"

# Exercise the compound value Forge supplies, then plain and unset variants.
for mode in compound plain fallback; do
    : > "$DEPLOY_TEST_LOG"
    case "$mode" in
        compound) export FORGE_COMPOSER='php8.3 /usr/local/bin/composer' ;;
        plain) export FORGE_COMPOSER=composer ;;
        fallback) unset FORGE_COMPOSER ;;
    esac
    bash "$script_dir/forge-deploy.sh" > "$test_dir/output.log"
    first=$(head -n 1 "$DEPLOY_TEST_LOG")
    if [[ "$mode" == compound ]]; then
        expected='/usr/local/bin/composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev'
    else
        expected='composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev'
    fi
    [[ "$first" == "$expected" ]] || { printf 'Wrong Composer invocation: %s\n' "$first" >&2; exit 1; }
    grep -Fxq 'artisan queue:verify-horizon --restart --wait=90' "$DEPLOY_TEST_LOG"
done
printf 'Forge deploy shell regression passed: compound, plain, and fallback Composer commands.\n'
