<?php
/**
 * Setup Analytics Tables
 * 
 * Simple script to create BigQuery tables for analytics
 */



echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Setup Analytics Tables</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>";
echo "body { background-color: #f8f9fa; padding: 20px; }";
echo ".status-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }";
echo ".success { border-left: 4px solid #28a745; }";
echo ".error { border-left: 4px solid #dc3545; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Setup Analytics Tables</h1>";
echo "<p class='text-muted'>Creating BigQuery tables for analytics...</p>";

try {
    // Include required files
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../app/Classes/BigQueryHelper.php';
    
    $bigQueryHelper = new App\Classes\BigQueryHelper();
    $bigQuery = $bigQueryHelper->getBigQueryClient();
    $projectId = $bigQueryHelper->getProjectId();
    
    echo "<div class='status-card success'>";
    echo "<h4>✅ BigQuery Connection</h4>";
    echo "<p>Connected to project: <strong>{$projectId}</strong></p>";
    echo "</div>";
    
    // Step 1: Create dataset
    echo "<div class='status-card'>";
    echo "<h4>📁 Creating Dataset</h4>";
    
    try {
        $dataset = $bigQuery->dataset('booking_analytics');
        if (!$dataset->exists()) {
            $dataset = $bigQuery->createDataset('booking_analytics');
            echo "<p class='text-success'>✅ Created dataset: booking_analytics</p>";
        } else {
            echo "<p class='text-success'>✅ Dataset already exists: booking_analytics</p>";
        }
    } catch (Exception $e) {
        echo "<p class='text-danger'>❌ Error creating dataset: " . $e->getMessage() . "</p>";
        throw $e;
    }
    echo "</div>";
    
    // Step 2: Create tables
    echo "<div class='status-card'>";
    echo "<h4>📋 Creating Tables</h4>";
    
    $tables = [
        'bookings' => [
            ['name' => 'booking_id', 'type' => 'INT64'],
            ['name' => 'destination', 'type' => 'STRING'],
            ['name' => 'pickup_point', 'type' => 'STRING'],
            ['name' => 'date_of_tour', 'type' => 'DATE'],
            ['name' => 'end_of_tour', 'type' => 'DATE'],
            ['name' => 'number_of_days', 'type' => 'INT64'],
            ['name' => 'number_of_buses', 'type' => 'INT64'],
            ['name' => 'balance', 'type' => 'FLOAT64'],
            ['name' => 'status', 'type' => 'STRING'],
            ['name' => 'payment_status', 'type' => 'STRING'],
            ['name' => 'user_id', 'type' => 'INT64'],
            ['name' => 'booked_at', 'type' => 'TIMESTAMP'],
            ['name' => 'pickup_time', 'type' => 'TIME'],
            ['name' => 'confirmed_at', 'type' => 'TIMESTAMP'],
            ['name' => 'payment_deadline', 'type' => 'TIMESTAMP'],
            ['name' => 'completed_at', 'type' => 'TIMESTAMP'],
            ['name' => 'created_by', 'type' => 'INT64']
        ],
        'daily_bookings' => [
            ['name' => 'date', 'type' => 'DATE'],
            ['name' => 'total_bookings', 'type' => 'INT64'],
            ['name' => 'total_revenue', 'type' => 'FLOAT64'],
            ['name' => 'confirmed_bookings', 'type' => 'INT64']
        ],
        'booking_details' => [
            ['name' => 'booking_id', 'type' => 'INT64'],
            ['name' => 'number_of_days', 'type' => 'INT64'],
            ['name' => 'price', 'type' => 'FLOAT64'],
            ['name' => 'day_of_week', 'type' => 'STRING'],
            ['name' => 'trip_duration_category', 'type' => 'STRING'],
            ['name' => 'price_category', 'type' => 'STRING']
        ]
    ];
    
    foreach ($tables as $tableName => $schema) {
        try {
            $table = $dataset->table($tableName);
            if (!$table->exists()) {
                $table = $dataset->createTable($tableName, [
                    'schema' => [
                        'fields' => $schema
                    ]
                ]);
                echo "<p class='text-success'>✅ Created table: {$tableName}</p>";
            } else {
                echo "<p class='text-success'>✅ Table already exists: {$tableName}</p>";
            }
        } catch (Exception $e) {
            echo "<p class='text-danger'>❌ Error creating {$tableName}: " . $e->getMessage() . "</p>";
        }
    }
    echo "</div>";
    
    // Step 3: Insert sample data
    echo "<div class='status-card'>";
    echo "<h4>📊 Inserting Sample Data</h4>";
    
    // Insert sample daily bookings data
    $sampleData = [
        ['date' => '2024-01-01', 'total_bookings' => 5, 'total_revenue' => 2500.00, 'confirmed_bookings' => 5],
        ['date' => '2024-01-02', 'total_bookings' => 8, 'total_revenue' => 4000.00, 'confirmed_bookings' => 8],
        ['date' => '2024-01-03', 'total_bookings' => 3, 'total_revenue' => 1500.00, 'confirmed_bookings' => 3],
        ['date' => '2024-01-04', 'total_bookings' => 12, 'total_revenue' => 6000.00, 'confirmed_bookings' => 12],
        ['date' => '2024-01-05', 'total_bookings' => 7, 'total_revenue' => 3500.00, 'confirmed_bookings' => 7],
        ['date' => '2024-01-06', 'total_bookings' => 15, 'total_revenue' => 7500.00, 'confirmed_bookings' => 15],
        ['date' => '2024-01-07', 'total_bookings' => 9, 'total_revenue' => 4500.00, 'confirmed_bookings' => 9],
        ['date' => '2024-01-08', 'total_bookings' => 6, 'total_revenue' => 3000.00, 'confirmed_bookings' => 6],
        ['date' => '2024-01-09', 'total_bookings' => 11, 'total_revenue' => 5500.00, 'confirmed_bookings' => 11],
        ['date' => '2024-01-10', 'total_bookings' => 4, 'total_revenue' => 2000.00, 'confirmed_bookings' => 4]
    ];
    
    try {
        $table = $dataset->table('daily_bookings');
        $insertResponse = $table->insertRows($sampleData);
        echo "<p class='text-success'>✅ Inserted sample data into daily_bookings</p>";
    } catch (Exception $e) {
        echo "<p class='text-warning'>⚠️ Could not insert sample data: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
    
    // Success message
    echo "<div class='status-card success'>";
    echo "<h4>🎉 Setup Complete!</h4>";
    echo "<p>Your BigQuery analytics tables have been created successfully.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Train ML models for forecasting</li>";
    echo "<li>Run analytics queries</li>";
    echo "<li>View predictions and insights</li>";
    echo "</ul>";
    echo "<a href='../dashboard/simple_analytics.php' class='btn btn-primary'>Go to Analytics Dashboard</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='status-card error'>";
    echo "<h4>❌ Setup Error</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your BigQuery configuration and try again.</p>";
    echo "</div>";
}

echo "</div>"; // Close container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body>";
echo "</html>"; 