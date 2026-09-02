#!/usr/bin/env bash
# Runs php-cs-fixer + phpstan inside Docker on a PHP file edited by Claude.
# Reads the hook payload JSON from stdin, extracts the file path, routes to
# the correct container (api vs intranet), runs both tools, and exits with
# code 2 (blocking error back to the model) on failure.

set -u

payload=$(cat)
file=$(printf '%s' "$payload" | jq -r '.tool_input.file_path // .tool_response.filePath // empty')

if [ -z "$file" ] || [[ "$file" != *.php ]]; then
    exit 0
fi

repo_root="${CLAUDE_PROJECT_DIR:-$(cd "$(dirname "$0")/../.." && pwd)}"

run_and_report() {
    local label="$1"
    shift
    local out
    out=$("$@" 2>&1)
    local rc=$?
    if [ $rc -ne 0 ]; then
        printf '[%s] failed:\n%s\n' "$label" "$out" >&2
        return $rc
    fi
    return 0
}

case "$file" in
    "$repo_root"/api/*)
        rel="${file#"$repo_root"/api/}"
        run_and_report "api php-cs-fixer" \
            docker exec tld-php-api-1 vendor/bin/php-cs-fixer fix "$rel" || exit 2
        run_and_report "api phpstan" \
            docker exec tld-php-api-1 vendor/bin/phpstan analyse "$rel" || exit 2
        ;;
    "$repo_root"/intranet/*)
        rel="${file#"$repo_root"/intranet/}"
        run_and_report "intranet php-cs-fixer" \
            docker exec tld-gse-1 bash -c "cd /srv/alvest-web-portals/intranet && vendor/bin/php-cs-fixer fix '$rel'" || exit 2
        run_and_report "intranet phpstan" \
            docker exec tld-gse-1 bash -c "cd /srv/alvest-web-portals/intranet && vendor/bin/phpstan analyse -c phpstan.neon '$rel'" || exit 2
        ;;
    "$repo_root"/extranet-new/*)
        rel="${file#"$repo_root"/extranet-new/}"
        run_and_report "extranet-new php-cs-fixer" \
            docker exec tld-extranet-php-1 vendor/bin/php-cs-fixer fix "$rel" || exit 2
        run_and_report "extranet-new phpstan" \
            docker exec tld-extranet-php-1 vendor/bin/phpstan analyse "$rel" || exit 2
        ;;
    *)
        # Other apps (shopfloor, evendors, packages, ...) are not
        # wired in yet — add new cases here if needed.
        exit 0
        ;;
esac

exit 0
