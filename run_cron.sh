#!/bin/bash

# =============================================================================
# Malware Pipeline Runner for cPanel
# =============================================================================
# Script ini menjalankan pipeline import CSV VPS lalu deteksi malware.
# Jadwal menit/jam diatur langsung dari cPanel Cron Jobs.
#
# Contoh schedule cPanel tiap 4 jam:
# Minute: 0
# Hour: */4
# Day: *
# Month: *
# Weekday: *
#
# Command:
# /bin/bash /home/detectio/laravel-imbalanced-data-system-web/run_cron.sh >> /dev/null 2>&1
#
# IMPORTANT: 
# 1. Ganti PROJECT_PATH jika nama user/path cPanel berbeda.
# 2. Ganti PHP_PATH jika diperlukan. Cek via SSH: which php
# 3. Set permission: chmod +x run_cron.sh
# =============================================================================

PROJECT_PATH="${PROJECT_PATH:-/home/detectio/laravel-imbalanced-data-system-web}"
PHP_PATH="${PHP_PATH:-/usr/local/bin/php}"
LOG_FILE="${PROJECT_PATH}/storage/logs/cron.log"
IMPORT_LIMIT="${IMPORT_LIMIT:-10}"
BATCH_SIZE="${BATCH_SIZE:-100}"
DETECT_LIMIT="${DETECT_LIMIT:-}"

# Pindah ke direktori project
cd "${PROJECT_PATH}" || exit 1

# Pastikan direktori log exists
mkdir -p "${PROJECT_PATH}/storage/logs"

# Jalankan pipeline sekali. Jadwalnya dikendalikan oleh cPanel Cron Jobs.
COMMAND=("${PHP_PATH}" artisan malware:run-pipeline --import-limit="${IMPORT_LIMIT}" --batch-size="${BATCH_SIZE}")

if [ -n "${DETECT_LIMIT}" ]; then
    COMMAND+=(--detect-limit="${DETECT_LIMIT}")
fi

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Running malware pipeline..." >> "${LOG_FILE}"
"${COMMAND[@]}" >> "${LOG_FILE}" 2>&1
STATUS=$?
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Malware pipeline finished with status ${STATUS}" >> "${LOG_FILE}"

# Exit dengan status code dari artisan
exit ${STATUS}
