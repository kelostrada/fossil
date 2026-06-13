#!/usr/bin/env bash
# UI-test screenshotter.
#
# Renders templates/layout.php with mock content (tools/uitest/preview.php)
# across a matrix of design × mode × viewport × page, and saves PNGs to
# tools/uitest/shots/ for visual review.
#
# Requires Docker (php:8.2-cli + zenika/alpine-chrome). No DB needed.
#
# Usage:
#   ./shoot.sh                       # full matrix (all designs, both modes, desktop+mobile, dashboard)
#   ./shoot.sh --design 3 --view mobile --mode dark --page online
#   ./shoot.sh --design 3 --view mobile --menu      # force the mobile drawer open
#
# Options (repeatable lists are comma-separated):
#   --design 1,3,8     designs to render (default: 1..8)
#   --mode light,dark  (default: light,dark)
#   --view desktop,mobile  (default: desktop,mobile)
#   --page dashboard|online|killers|wide  (default: dashboard)
#   --menu             force the mobile sidebar drawer open
set -euo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
OUT="$REPO/tools/uitest/out"
SHOTS="$REPO/tools/uitest/shots"
PHP_IMG="php:8.2-cli"
CHROME_IMG="zenika/alpine-chrome"

designs="1,2,3,4,5,6,7,8"
modes="light,dark"
views="desktop,mobile"
page="dashboard"
menu=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --design) designs="$2"; shift 2 ;;
    --mode)   modes="$2"; shift 2 ;;
    --view)   views="$2"; shift 2 ;;
    --page)   page="$2"; shift 2 ;;
    --menu)   menu="1"; shift ;;
    *) echo "unknown option: $1" >&2; exit 1 ;;
  esac
done

mkdir -p "$OUT" "$SHOTS"

dims() { # viewport -> WxH
  case "$1" in
    mobile)  echo "390,1700" ;;
    desktop) echo "1280,1600" ;;
    *) echo "1280,1600" ;;
  esac
}

shoot_one() {
  local design="$1" mode="$2" view="$3"
  local dark=0; [[ "$mode" == "dark" ]] && dark=1
  local tag="d${design}_${mode}_${view}_${page}${menu:+_menu}"
  local html="$OUT/$tag.html"
  local png="$SHOTS/$tag.png"
  rm -f "$png"

  DESIGN="$design" DARK="$dark" PAGE="$page" MENU="${menu:-}" \
    docker run --rm -e DESIGN -e DARK -e PAGE -e MENU -v "$REPO":/app -w /app \
      "$PHP_IMG" php tools/uitest/preview.php > "$html"

  local rel="tools/uitest/out/$tag.html"
  docker rm -f "fvshot_$tag" >/dev/null 2>&1 || true
  docker run -d --name "fvshot_$tag" -v "$REPO":/app -w /app "$CHROME_IMG" \
    --no-sandbox --hide-scrollbars --window-size="$(dims "$view")" --virtual-time-budget=6000 \
    --screenshot="/app/tools/uitest/shots/$tag.png" "file:///app/$rel" >/dev/null 2>&1

  local i=0
  while [[ $i -lt 45 && ! -f "$png" ]]; do sleep 2; i=$((i+2)); done
  docker kill "fvshot_$tag" >/dev/null 2>&1 || true
  docker rm -f "fvshot_$tag" >/dev/null 2>&1 || true

  if [[ -f "$png" ]]; then
    echo "OK   $tag ($(wc -c < "$png" | tr -d ' ') bytes)"
  else
    echo "FAIL $tag (no screenshot after ${i}s)"
  fi
}

IFS=',' read -ra D <<< "$designs"
IFS=',' read -ra M <<< "$modes"
IFS=',' read -ra V <<< "$views"
for d in "${D[@]}"; do
  for m in "${M[@]}"; do
    for v in "${V[@]}"; do
      shoot_one "$d" "$m" "$v"
    done
  done
done

echo "---"
echo "Screenshots in: $SHOTS"
