#!/usr/bin/env bash
# Deploys Fossil Stats to the production server via rsync over SSH.
# Usage:
#   ./deploy.sh            # shows a dry-run preview, then prompts before deploying
#   ./deploy.sh --dry-run  # preview only, no deploy
set -euo pipefail

SSH_USER=srv34353
SSH_HOST=h18.seohost.pl
SSH_PORT=57185
REMOTE_PATH='domains/fossil.kelostrada.pl/public_html/'

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

DRY_RUN=false
if [[ "${1:-}" == "--dry-run" || "${1:-}" == "-n" ]]; then
  DRY_RUN=true
fi

RSYNC_OPTS=(
  -avz
  --human-readable
  --itemize-changes
  -e "ssh -p ${SSH_PORT}"
  --exclude='.git/'
  --exclude='.gitignore'
  --exclude='.env'
  --exclude='.DS_Store'
  --exclude='*.log'
  --exclude='page.txt'
  --exclude='type.txt'
  --exclude='last_fetched_id.txt'
  --exclude='notified_logins.json'
  --exclude='deploy.sh'
  --exclude='README.md'
)

REMOTE="${SSH_USER}@${SSH_HOST}:${REMOTE_PATH}"

echo "=== Dry-run preview ==="
echo "Source:  ${SCRIPT_DIR}/"
echo "Target:  ${REMOTE}"
echo
rsync "${RSYNC_OPTS[@]}" --dry-run "${SCRIPT_DIR}/" "${REMOTE}"

if $DRY_RUN; then
  echo
  echo "Dry-run only. Re-run without --dry-run to deploy."
  exit 0
fi

echo
read -rp "Proceed with deployment? [y/N] " ans
[[ "$ans" =~ ^[Yy]$ ]] || { echo "Aborted."; exit 1; }

echo
echo "=== Deploying ==="
rsync "${RSYNC_OPTS[@]}" "${SCRIPT_DIR}/" "${REMOTE}"

echo
echo "Done."
