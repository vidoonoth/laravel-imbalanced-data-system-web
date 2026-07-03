<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'ip_geolocation' => [
        'base_url' => env('IP_GEOLOCATION_BASE_URL', 'https://ipwho.is'),
        'cache_ttl' => env('IP_GEOLOCATION_CACHE_TTL', 60 * 60 * 24 * 30),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'vps' => [
        'host' => env('VPS_HOST'),
        'port' => env('VPS_PORT', 22),
        'username' => env('VPS_USERNAME'),
        'private_key' => env('VPS_PRIVATE_KEY') ? str_replace('\\n', "\n", env('VPS_PRIVATE_KEY')) : null,
        'private_key_path' => env('VPS_PRIVATE_KEY_PATH'),
        'target_dir' => env('VPS_TARGET_DIR'),
    ],

    'vps_csv' => [
        'enabled' => env('VPS_CSV_ENABLED', false),
        'schedule' => env('VPS_CSV_SCHEDULE', 'every_four_hours'),
        'schedule_time' => env('VPS_CSV_SCHEDULE_TIME', '12:55'),
        'schedule_timezone' => env('VPS_CSV_SCHEDULE_TIMEZONE', 'Asia/Jakarta'),
        'max_files' => (int) env('VPS_CSV_MAX_FILES', 10),
        'delete_after_processing' => env('VPS_CSV_DELETE_AFTER_PROCESSING', false),
        'host' => env('VPS_HOST'),
        'port' => (int) env('VPS_PORT', 22),
        'username' => env('VPS_USERNAME'),
        'source_dir' => env('VPS_SOURCE_DIR', '/var/www/syslog-datasets'),
        'base_url' => env('VPS_BASE_URL'),
        'private_key' => env('VPS_PRIVATE_KEY') ? str_replace('\\n', "\n", env('VPS_PRIVATE_KEY')) : null,
        'private_key_path' => env('VPS_PRIVATE_KEY_PATH'),
        'strict_host_key_checking' => env('VPS_STRICT_HOST_KEY_CHECKING', 'accept-new'),
        'connect_timeout' => (int) env('VPS_CSV_CONNECT_TIMEOUT', 15),
        'timeout' => (int) env('VPS_CSV_TIMEOUT', 120),
        'has_header' => env('VPS_CSV_HAS_HEADER', true),
        'delimiter' => env('VPS_CSV_DELIMITER', ','),
        'local_dir' => env('VPS_CSV_LOCAL_DIR', 'datasets/vps'),
        'keep_local_copy' => env('VPS_CSV_KEEP_LOCAL_COPY', false),
    ],

    'ml' => [
        'enabled' => env('ML_DETECTION_ENABLED', true),
        'schedule' => env('ML_DETECTION_SCHEDULE', 'every_four_hours'),
        'schedule_time' => env('ML_DETECTION_SCHEDULE_TIME', '13:00'),
        'schedule_timezone' => env('ML_DETECTION_SCHEDULE_TIMEZONE', 'Asia/Jakarta'),
        'api_key' => env('ML_API_KEY'),
        'flask_url' => env('ML_FLASK_URL', 'http://127.0.0.1:5000'),
        'timeout' => (int) env('ML_DETECTION_TIMEOUT', 60),
        'batch_size' => (int) env('ML_DETECTION_BATCH_SIZE', 100),
    ],

    'malware_pipeline' => [
        'enabled' => env('MALWARE_PIPELINE_ENABLED', false),
        'schedule' => env('MALWARE_PIPELINE_SCHEDULE', 'every_four_hours'),
        'schedule_time' => env('MALWARE_PIPELINE_SCHEDULE_TIME', '00:00'),
        'schedule_timezone' => env('MALWARE_PIPELINE_SCHEDULE_TIMEZONE', 'Asia/Jakarta'),
        'import_limit' => (int) env('MALWARE_PIPELINE_IMPORT_LIMIT', env('VPS_CSV_MAX_FILES', 10)),
        'detection_limit' => env('MALWARE_PIPELINE_DETECTION_LIMIT') !== null && env('MALWARE_PIPELINE_DETECTION_LIMIT') !== ''
            ? (int) env('MALWARE_PIPELINE_DETECTION_LIMIT')
            : null,
        'batch_size' => (int) env('MALWARE_PIPELINE_BATCH_SIZE', env('ML_DETECTION_BATCH_SIZE', 100)),
        'without_overlapping_minutes' => (int) env('MALWARE_PIPELINE_LOCK_MINUTES', 240),
    ],

];
