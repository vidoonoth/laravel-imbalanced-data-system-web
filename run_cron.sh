#!/bin/bash

# =============================================================================
# Laravel Schedule Runner for cPanel
# =============================================================================
# Script ini menjalankan Laravel scheduler untuk cPanel hosting
# Tambahkan ke cPanel Cron Jobs dengan schedule: * * * * *
# Command: /bin/bash /home/username/public_html/run_cron.sh >> /dev/null 2>&1
#
# IMPORTANT: 
# 1. Ganti /home/username/public_html dengan path project Anda
# 2. Ganti path PHP jika diperlukan (gunakan: which php di SSH)
# 3. Set permission: chmod +x run_cron.sh
# =============================================================================

# Configuration - SESUAIKAN DENGAN ENVIRONMENT ANDA
PROJECT_PATH="/home/detectio/laravel-imbalanced-data-system-web"
PHP_PATH="/usr/local/bin/php"
LOG_FILE="${PROJECT_PATH}/storage/logs/cron.log"

# Pindah ke direktori project
cd "${PROJECT_PATH}" || exit 1

# Pastikan direktori log exists
mkdir -p "${PROJECT_PATH}/storage/logs"

# Jalankan Laravel scheduler
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Running Laravel scheduler..." >> "${LOG_FILE}"
"${PHP_PATH}" artisan schedule:run >> "${LOG_FILE}" 2>&1

# Exit dengan status code dari artisan
exit $?
