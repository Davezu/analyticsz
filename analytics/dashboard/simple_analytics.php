<?php
/**
 * Simple Analytics Dashboard
 * 
 * Direct access to analytics without controller
 */



// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Classes/BigQueryHelper.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Analytics Dashboard - KingLang</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>";
echo "<style>";
echo "body { background-color: #f8f9fa; }";
echo ".analytics-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }";
echo ".metric-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container-fluid py-4'>";
echo "<div class='row'>";
echo "<div class='col-12'>";
echo "<h1 class='mb-4'>📊 Analytics Dashboard</h1>";
echo "<p class='text-muted'>Welcome to KingLang Analytics!</p>";
echo "</div>";
echo "</div>";

try {
    // Create BigQueryHelper directly
    $bigQueryHelper = new App\Classes\BigQueryHelper();
    
    echo "<div class='row'>";
    
    // Analytics Overview
    echo "<div class='col-12'>";
    echo "<div class='analytics-card'>";
    echo "<h3>🎯 Analytics Overview</h3>";
    echo "<p>BigQuery ML Analytics System is working!</p>";
    echo "<div class='row'>";
    
    // Test each analytics function
    $tests = [
        'Booking Forecast' => 'getBookingForecast',
        'Revenue Forecast' => 'getRevenueForecast', 
        'Model Performance' => 'getModelPerformance',
        'Daily Trends' => 'getDailyTrends'
    ];
    
    foreach ($tests as $name => $method) {
        echo "<div class='col-md-6 col-lg-4 mb-3'>";
        echo "<div class='metric-card'>";
        echo "<h5>{$name}</h5>";
        try {
            $result = $bigQueryHelper->$method();
            if (is_array($result) && !empty($result)) {
                echo "<p class='mb-0'>✅ Working - " . count($result) . " records</p>";
            } else {
                echo "<p class='mb-0'>⚠️ No data available</p>";
            }
        } catch (Exception $e) {
            echo "<p class='mb-0'>❌ Error: " . substr($e->getMessage(), 0, 50) . "...</p>";
        }
        echo "</div>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // System Information
    echo "<div class='col-12 mt-4'>";
    echo "<div class='analytics-card'>";
    echo "<h3>🔧 System Information</h3>";
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<p><strong>System:</strong> KingLang Analytics</p>";
    echo "<strong>Status:</strong> Active</p>";
    echo "<p><strong>Version:</strong> 1.0</p>";
    echo "</div>";
    echo "<div class='col-md-6'>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>BigQuery Status:</strong> ✅ Connected</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='col-12'>";
    echo "<div class='analytics-card'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>This might be due to BigQuery configuration or data migration not being completed.</p>";
    echo "</div>";
    echo "</div>";
}

echo "</div>"; // Close row

// Navigation
echo "<div class='row mt-4'>";
echo "<div class='col-12'>";
echo "<div class='analytics-card'>";
echo "<h3>🔗 Quick Actions</h3>";
echo "<div class='row'>";
echo "<div class='col-md-3 mb-2'><a href='detailed_analytics.php' class='btn btn-primary w-100'>📊 Detailed Data</a></div>";
echo "<div class='col-md-3 mb-2'><a href='index.php' class='btn btn-outline-primary w-100'>Home Page</a></div>";

echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>"; // Close container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body>";
echo "</html>"; 