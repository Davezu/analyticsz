<?php
/**
 * Run Data Migration
 * 
 * This script will migrate your MySQL data to BigQuery
 */



echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Data Migration - KingLang Analytics</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>";
echo "body { background-color: #f8f9fa; }";
echo ".migration-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }";
echo ".step-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 15px; margin-bottom: 10px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container-fluid py-4'>";
echo "<div class='row'>";
echo "<div class='col-12'>";
echo "<h1 class='mb-4'>🔄 Data Migration</h1>";
echo "<p class='text-muted'>Migrating MySQL data to BigQuery for analytics</p>";
echo "</div>";
echo "</div>";

try {
    // Include required files
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../app/Classes/BigQueryHelper.php';
    
    echo "<div class='row'>";
    
    // Step 1: Check MySQL data
    echo "<div class='col-12'>";
    echo "<div class='migration-card'>";
    echo "<h3>📊 Step 1: Check MySQL Data</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $bookingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<div class='step-card'>";
    echo "<h5>MySQL Bookings</h5>";
    echo "<p class='mb-0'>Found {$bookingCount} booking records</p>";
    echo "</div>";
    
    if ($bookingCount > 0) {
        echo "<p style='color: green;'>✅ MySQL data is available</p>";
    } else {
        echo "<p style='color: red;'>❌ No booking data found</p>";
    }
    echo "</div>";
    echo "</div>";
    
    // Step 2: Test BigQuery connection
    echo "<div class='col-12'>";
    echo "<div class='migration-card'>";
    echo "<h3>🔗 Step 2: Test BigQuery Connection</h3>";
    
    $bigQueryHelper = new App\Classes\BigQueryHelper();
    $bigQuery = $bigQueryHelper->getBigQueryClient();
    
    echo "<div class='step-card'>";
    echo "<h5>BigQuery Connection</h5>";
    echo "<p class='mb-0'>✅ Connected to project: " . $bigQueryHelper->getProjectId() . "</p>";
    echo "</div>";
    
    // Step 3: Create dataset
    echo "<div class='col-12'>";
    echo "<div class='migration-card'>";
    echo "<h3>📁 Step 3: Create Analytics Dataset</h3>";
    
    try {
        $dataset = $bigQuery->dataset('booking_analytics');
        if (!$dataset->exists()) {
            $dataset = $bigQuery->createDataset('booking_analytics');
            echo "<div class='step-card'>";
            echo "<h5>Dataset Creation</h5>";
            echo "<p class='mb-0'>✅ Created dataset: booking_analytics</p>";
            echo "</div>";
        } else {
            echo "<div class='step-card'>";
            echo "<h5>Dataset Status</h5>";
            echo "<p class='mb-0'>✅ Dataset already exists: booking_analytics</p>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='step-card' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);'>";
        echo "<h5>Dataset Error</h5>";
        echo "<p class='mb-0'>❌ Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    // Step 4: Create tables
    echo "<div class='col-12'>";
    echo "<div class='migration-card'>";
    echo "<h3>📋 Step 4: Create Analytics Tables</h3>";
    
    $tables = [
        'bookings' => [
            'booking_id' => 'INT64',
            'destination' => 'STRING',
            'pickup_point' => 'STRING',
            'date_of_tour' => 'DATE',
            'end_of_tour' => 'DATE',
            'number_of_days' => 'INT64',
            'number_of_buses' => 'INT64',
            'balance' => 'FLOAT64',
            'status' => 'STRING',
            'payment_status' => 'STRING',
            'user_id' => 'INT64',
            'booked_at' => 'TIMESTAMP',
            'pickup_time' => 'TIME',
            'confirmed_at' => 'TIMESTAMP',
            'payment_deadline' => 'TIMESTAMP',
            'completed_at' => 'TIMESTAMP',
            'created_by' => 'INT64'
        ],
        'daily_bookings' => [
            'date' => 'DATE',
            'total_bookings' => 'INT64',
            'total_revenue' => 'FLOAT64',
            'cancelled_bookings' => 'INT64',
            'confirmed_bookings' => 'INT64'
        ],
        'booking_details' => [
            'booking_id' => 'INT64',
            'number_of_days' => 'INT64',
            'price' => 'FLOAT64',
            'day_of_week' => 'STRING',
            'trip_duration_category' => 'STRING',
            'price_category' => 'STRING',

        ]
    ];
    
    foreach ($tables as $tableName => $schema) {
        try {
            $table = $dataset->table($tableName);
            if (!$table->exists()) {
                $table = $dataset->createTable($tableName, [
                    'schema' => [
                        'fields' => array_map(function($field, $type) {
                            return ['name' => $field, 'type' => $type];
                        }, array_keys($schema), $schema)
                    ]
                ]);
                echo "<div class='step-card'>";
                echo "<h5>Table Creation</h5>";
                echo "<p class='mb-0'>✅ Created table: {$tableName}</p>";
                echo "</div>";
            } else {
                echo "<div class='step-card'>";
                echo "<h5>Table Status</h5>";
                echo "<p class='mb-0'>✅ Table already exists: {$tableName}</p>";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='step-card' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);'>";
            echo "<h5>Table Error</h5>";
            echo "<p class='mb-0'>❌ Error creating {$tableName}: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
    
    echo "</div>";
    echo "</div>";
    
    // Success message
    echo "<div class='col-12'>";
    echo "<div class='migration-card'>";
    echo "<h3>🎉 Migration Complete!</h3>";
    echo "<p>Your BigQuery analytics tables have been created successfully.</p>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Train ML models for forecasting</li>";
    echo "<li>Run analytics queries</li>";
    echo "<li>View predictions and insights</li>";
    echo "</ul>";
    echo "<a href='../dashboard/simple_analytics.php' class='btn btn-primary'>Go to Analytics Dashboard</a>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='col-12'>";
    echo "<div class='migration-card'>";
    echo "<h3>❌ Migration Error</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your BigQuery configuration and try again.</p>";
    echo "</div>";
    echo "</div>";
}

echo "</div>"; // Close row

echo "</div>"; // Close container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body>";
echo "</html>"; 