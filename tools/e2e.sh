#!/usr/bin/env bash
# End-to-end smoke test: hits every public page, verifies it loads (HTTP 200),
# contains expected content, has no PHP errors, and responds within a time
# budget. Intended to run after each deploy.
#
# Usage:
#   tools/e2e.sh                         # test production
#   tools/e2e.sh https://staging.host    # test another base URL
#
# Exit code 0 = all passed, 1 = one or more failures.

BASE="${1:-https://fossil.kelostrada.pl}"
BUDGET="${E2E_BUDGET:-3.0}"   # seconds; warn if a page is slower
fails=0
warns=0

red()   { printf '\033[31m%s\033[0m' "$1"; }
green() { printf '\033[32m%s\033[0m' "$1"; }
yellow(){ printf '\033[33m%s\033[0m' "$1"; }

# check <path> <expect-substring> [must-not-contain]
check() {
  local path="$1" expect="$2" forbid="${3:-}"
  local url="$BASE/$path"
  local body http time
  body=$(curl -s -A "fossil-e2e" -w '\n%{http_code} %{time_total}' "$url")
  http=$(printf '%s' "$body" | tail -1 | awk '{print $1}')
  time=$(printf '%s' "$body" | tail -1 | awk '{print $2}')
  body=$(printf '%s' "$body" | sed '$d')

  local status="OK" ok=1
  if [ "$http" != "200" ]; then status="HTTP $http"; ok=0; fi
  if ! printf '%s' "$body" | grep -qiF "$expect"; then status="missing: '$expect'"; ok=0; fi
  if printf '%s' "$body" | grep -qiE "Fatal error|Parse error|Warning:|Notice:|Uncaught"; then status="PHP error in output"; ok=0; fi
  if [ -n "$forbid" ] && printf '%s' "$body" | grep -qiF "$forbid"; then status="unexpected: '$forbid'"; ok=0; fi

  local slow=""
  if awk "BEGIN{exit !($time > $BUDGET)}"; then slow=" $(yellow "[slow ${time}s]")"; warns=$((warns+1)); fi

  if [ "$ok" = 1 ]; then
    printf '  %s  %-45s %ss%s\n' "$(green PASS)" "$path" "$time" "$slow"
  else
    printf '  %s  %-45s %ss  %s\n' "$(red FAIL)" "$path" "$time" "$(red "$status")"
    fails=$((fails+1))
  fi
}

echo "E2E against $BASE (budget ${BUDGET}s)"
echo "------------------------------------------------------------"
check "index.php"                          "Welcome to Fossil Stats"
check "online.php"                         "Online Activity"
check "advancements.php"                   "Recent Skill Changes"  "No recent advancements found"
check "recent_deaths.php"                  "Recent Deaths"
check "highscores.php"                     "Highscores"
check "highscores.php?type=8"              "Highscores"
check "playerkillers.php"                  "Player Killers Ranking"
check "environmental_killers.php"          "Deadliest Creatures"
check "calculators.php"                    "Calculators"
check "chart.php?name=Robert"              "Robert"
check "getData.php?person=Robert&startDate=2024-06-13&endDate=2025-06-13" "timestamps"
check "search_characters.php?q=Rob"        "["
echo "------------------------------------------------------------"
if [ "$fails" -eq 0 ]; then
  printf '%s — %s warning(s)\n' "$(green "ALL PASSED")" "$warns"
  exit 0
else
  printf '%s — %s failure(s), %s warning(s)\n' "$(red "FAILED")" "$fails" "$warns"
  exit 1
fi
