<?php
/**
 * Analytics Configuration
 * 
 * This file contains configuration settings for BigQuery ML analytics
 */

// BigQuery Configuration
function get_project_id_from_credentials() {
    $credentialsPath = __DIR__ . '/../storage/google-credentials.json';
    if (file_exists($credentialsPath)) {
        $credentials = json_decode(file_get_contents($credentialsPath), true);
        if (json_last_error() === JSON_ERROR_NONE && isset($credentials['project_id'])) {
            return $credentials['project_id'];
        }
    }
    return getenv('GOOGLE_CLOUD_PROJECT_ID') ?: '118413093622862450573';
}

define('BIGQUERY_PROJECT_ID', get_project_id_from_credentials());
define('BIGQUERY_DATASET_ID', 'booking_analytics');
define('BIGQUERY_CREDENTIALS_PATH', __DIR__ . '/../storage/google-credentials.json');

// Analytics Settings
define('ANALYTICS_ENABLED', true);
define('ANALYTICS_MIN_DATA_DAYS', 90);
define('ANALYTICS_MIN_BOOKINGS', 100);

// Model Configuration
define('BOOKING_FORECAST_HORIZON', 30); // days
define('REVENUE_FORECAST_HORIZON', 30); // days

// Cache Settings
define('ANALYTICS_CACHE_ENABLED', true);
define('ANALYTICS_CACHE_DURATION', 3600); // 1 hour

/**
 * Get BigQuery configuration
 */
function get_bigquery_config() {
    return [
        'project_id' => BIGQUERY_PROJECT_ID,
        'dataset_id' => BIGQUERY_DATASET_ID,
        'credentials_path' => BIGQUERY_CREDENTIALS_PATH,
        'enabled' => ANALYTICS_ENABLED
    ];
}

/**
 * Check if analytics is properly configured
 */
function is_analytics_configured() {
    $config = get_bigquery_config();
    
    // Check if credentials file exists
    if (!file_exists($config['credentials_path'])) {
        return false;
    }
    
    // Check if project ID is set
    if ($config['project_id'] === 'your-project-id') {
        return false;
    }
    
    return true;
}

/**
 * Get analytics status
 */
function get_analytics_status() {
    $status = [
        'enabled' => ANALYTICS_ENABLED,
        'configured' => is_analytics_configured(),
        'credentials_file' => file_exists(BIGQUERY_CREDENTIALS_PATH),
        'project_id' => BIGQUERY_PROJECT_ID !== 'your-project-id'
    ];
    
    return $status;
} 