# Production cPanel Deployment

Panduan ini mengikuti struktur hosting:

```text
/home/detectio/
+-- public_html/
+-- laravel-imbalanced-data-system-web/
```

## 1. Upload Folder ML

Upload folder `ml` dari root project lokal ke:

```text
/home/detectio/laravel-imbalanced-data-system-web/ml
```

Isi minimal:

```text
ml/
+-- app.py
+-- detection_service.py
+-- requirements.txt
+-- model_artifacts/
    +-- feature_columns.json
    +-- preprocessor.joblib
    +-- scarf_encoder.pt
    +-- scarf_classifier.pt
```

Jangan upload folder `ml/venv` dari Windows. Buat virtualenv baru dari cPanel.

## 2. Setup Python App Di cPanel

Di cPanel, buka `Setup Python App`, lalu buat aplikasi:

```text
Python version: 3.9+ atau 3.10+
Application root: laravel-imbalanced-data-system-web/ml
Application URL: detectionmalwareupatik.my.id/api/ml
Application startup file: app.py
Application entry point: application
```

Tambahkan environment variable:

```text
ML_API_KEY=isi_key_rahasia_yang_sama_dengan_laravel
```

Install dependency dari terminal cPanel:

```bash
cd /home/detectio/laravel-imbalanced-data-system-web/ml
source venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
deactivate
```

Restart Python App dari cPanel.

## 3. Setup Laravel Env

Copy isi `.env_production` ke `.env` server, lalu ganti:

```text
APP_KEY
DB_DATABASE
DB_USERNAME
DB_PASSWORD
ML_API_KEY
VPS_PRIVATE_KEY_PATH
```

Nilai penting:

```env
VPS_CSV_ENABLED=true
ML_DETECTION_ENABLED=true
ML_FLASK_URL=https://detectionmalwareupatik.my.id/api/ml
MALWARE_PIPELINE_IMPORT_LIMIT=10
MALWARE_PIPELINE_BATCH_SIZE=100
```

`MALWARE_PIPELINE_ENABLED=false` sengaja dibiarkan `false` untuk mode ini, karena jadwal dijalankan langsung oleh cPanel Cron Jobs, bukan Laravel scheduler.

Simpan private key SSH VPS di server, contoh:

```text
/home/detectio/laravel-alfin-key
```

Permission:

```bash
chmod 600 /home/detectio/laravel-alfin-key
```

## 4. Jalankan Setup Laravel

```bash
cd /home/detectio/laravel-imbalanced-data-system-web
php artisan migrate --force
php artisan storage:link
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

Jika sudah ada hasil deteksi lama dan ingin mengisi field tambahan:

```bash
php artisan detection:backfill-fields
```

## 5. Test Manual

Test Python API:

```bash
curl https://detectionmalwareupatik.my.id/api/ml/health
```

Test dari Laravel:

```bash
cd /home/detectio/laravel-imbalanced-data-system-web
php artisan detection:run-flask --check-health
php artisan datasets:import-vps --limit=1 --dry-run
php artisan malware:run-pipeline --import-limit=1 --detect-limit=10
```

Command pipeline akan:

1. Ambil CSV dari VPS.
2. Skip CSV yang source path-nya sudah pernah `completed`.
3. Simpan row baru ke tabel `datasets`.
4. Deteksi hanya dataset yang belum punya `detection_results`.
5. Simpan hasil deteksi dan tampilkan di dashboard.

## 6. Setup Cron Job cPanel

Di cPanel `Cron Jobs`, atur jadwal langsung di form cPanel. Contoh tiap 4 jam:

```text
Minute: 0
Hour: */4
Day: *
Month: *
Weekday: *
```

Command:

```bash
/bin/bash /home/detectio/laravel-imbalanced-data-system-web/run_cron.sh >> /dev/null 2>&1
```

Jadi cPanel yang menentukan jam/menit eksekusi. Script `run_cron.sh` hanya menjalankan pipeline satu kali setiap dipanggil.

Alternatif tanpa script:

```bash
/usr/local/bin/php /home/detectio/laravel-imbalanced-data-system-web/artisan malware:run-pipeline --import-limit=10 --batch-size=100 >> /home/detectio/laravel-imbalanced-data-system-web/storage/logs/cron.log 2>&1
```

## 7. Log Monitoring

```bash
tail -f /home/detectio/laravel-imbalanced-data-system-web/storage/logs/cron.log
tail -f /home/detectio/laravel-imbalanced-data-system-web/storage/logs/laravel.log
```

Untuk Python App, cek log dari menu `Setup Python App` di cPanel.
