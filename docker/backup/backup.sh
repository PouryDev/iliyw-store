#!/usr/bin/env sh
set -euo pipefail

# Env: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
STAMP=$(date +"%Y%m%d-%H%M%S")
TARGET_DIR=/backups
TMP_FILE="$TARGET_DIR/backup-$STAMP.sql.gz"
LATEST_LINK="$TARGET_DIR/latest.sql.gz"

mkdir -p "$TARGET_DIR"

mysqldump -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" --single-transaction --quick --routines --triggers "$DB_DATABASE" \
  | gzip -c > "$TMP_FILE"

ln -sf "$(basename "$TMP_FILE")" "$LATEST_LINK"

echo "Backup created: $TMP_FILE and updated latest.sql.gz"


