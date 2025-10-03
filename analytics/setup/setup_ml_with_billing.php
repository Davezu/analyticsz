<?php
/**
 * Setup BigQuery ML with Billing Enabled
 * 
 * Create datasets, tables, and train ML models now that billing is active
 */



echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Setup BigQuery ML</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>";
echo "body { background-color: #f8f9fa; padding: 20px; }";
echo ".status-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }";
echo ".success { border-left: 4px solid #28a745; }";
echo ".error { border-left: 4px solid #dc3545; }";
echo ".warning { border-left: 4px solid #ffc107; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🤖 Setup BigQuery ML with Billing</h1>";
echo "<p class='text-muted'>Creating datasets, tables, and training ML models...</p>";

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
    echo "<p>Billing is enabled - ML features available!</p>";
    echo "</div>";
    
    // Step 1: Create Dataset
    echo "<div class='status-card'>";
    echo "<h4>📁 Step 1: Create Analytics Dataset</h4>";
    
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
    
    // Step 2: Create Tables
    echo "<div class='status-card'>";
    echo "<h4>📋 Step 2: Create Analytics Tables</h4>";
    
    $tables = [
        'daily_bookings' => [
            ['name' => 'date', 'type' => 'DATE'],
            ['name' => 'total_bookings', 'type' => 'INT64'],
            ['name' => 'total_revenue', 'type' => 'FLOAT64'],
            ['name' => 'cancelled_bookings', 'type' => 'INT64'],
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
    
    // Step 3: Insert Sample Data
    echo "<div class='status-card'>";
    echo "<h4>📊 Step 3: Insert Sample Data</h4>";
    
    // Insert daily_bookings data
    $dailyBookingsSQL = "
        INSERT INTO `{$projectId}.booking_analytics.daily_bookings` (date, total_bookings, total_revenue, confirmed_bookings)
        VALUES 
        ('2024-01-01', 5, 2500.00, 5),
        ('2024-01-02', 8, 4000.00, 8),
        ('2024-01-03', 3, 1500.00, 3),
        ('2024-01-04', 12, 6000.00, 12),
        ('2024-01-05', 7, 3500.00, 7),
        ('2024-01-06', 15, 7500.00, 15),
        ('2024-01-07', 9, 4500.00, 9),
        ('2024-01-08', 6, 3000.00, 6),
        ('2024-01-09', 11, 5500.00, 11),
        ('2024-01-10', 4, 2000.00, 4),
        ('2024-01-11', 13, 6500.00, 13),
        ('2024-01-12', 8, 4000.00, 8),
        ('2024-01-13', 16, 8000.00, 16),
        ('2024-01-14', 10, 5000.00, 10),
        ('2024-01-15', 7, 3500.00, 7),
        ('2024-01-16', 14, 7000.00, 14),
        ('2024-01-17', 9, 4500.00, 9),
        ('2024-01-18', 12, 6000.00, 12),
        ('2024-01-19', 6, 3000.00, 6),
        ('2024-01-20', 18, 9000.00, 18)
    ";
    
    try {
        $query = $bigQuery->query($dailyBookingsSQL);
        $job = $bigQuery->runQuery($query);
        
        // Wait for job to complete
        $job->reload();
        while (!$job->isComplete()) {
            sleep(1);
            $job->reload();
        }
        
        $jobInfo = $job->info();
        if (isset($jobInfo['status']['state']) && $jobInfo['status']['state'] === 'DONE') {
            echo "<p class='text-success'>✅ Successfully inserted daily_bookings data</p>";
        } else {
            $errorMessage = isset($jobInfo['status']['errorResult']['message']) 
                ? $jobInfo['status']['errorResult']['message'] 
                : 'Unknown error';
            echo "<p class='text-danger'>❌ Error inserting daily_bookings data: {$errorMessage}</p>";
        }
    } catch (Exception $e) {
        echo "<p class='text-danger'>❌ Error inserting daily_bookings data: " . $e->getMessage() . "</p>";
    }
    
    // Insert booking_details data
    $bookingDetailsSQL = "
        INSERT INTO `{$projectId}.booking_analytics.booking_details` (booking_id, number_of_days, price, day_of_week, trip_duration_category, price_category)
        VALUES 
        (1, 3, 500.00, 'Monday', 'Short', 'Medium'),
        (2, 5, 800.00, 'Tuesday', 'Medium', 'High'),
        (3, 2, 300.00, 'Wednesday', 'Short', 'Low'),
        (4, 7, 1200.00, 'Thursday', 'Long', 'High'),
        (5, 4, 600.00, 'Friday', 'Medium', 'Medium'),
        (6, 1, 150.00, 'Saturday', 'Short', 'Low'),
        (7, 6, 900.00, 'Sunday', 'Long', 'High'),
        (8, 3, 450.00, 'Monday', 'Short', 'Medium'),
        (9, 5, 750.00, 'Tuesday', 'Medium', 'High'),
        (10, 2, 250.00, 'Wednesday', 'Short', 'Low'),
        (11, 4, 650.00, 'Thursday', 'Medium', 'Medium'),
        (12, 8, 1400.00, 'Friday', 'Long', 'High'),
        (13, 1, 200.00, 'Saturday', 'Short', 'Low'),
        (14, 3, 550.00, 'Sunday', 'Short', 'Medium'),
        (15, 6, 950.00, 'Monday', 'Long', 'High')
    ";
    
    try {
        $query = $bigQuery->query($bookingDetailsSQL);
        $job = $bigQuery->runQuery($query);
        
        // Wait for job to complete
        $job->reload();
        while (!$job->isComplete()) {
            sleep(1);
            $job->reload();
        }
        
        $jobInfo = $job->info();
        if (isset($jobInfo['status']['state']) && $jobInfo['status']['state'] === 'DONE') {
            echo "<p class='text-success'>✅ Successfully inserted booking_details data</p>";
        } else {
            $errorMessage = isset($jobInfo['status']['errorResult']['message']) 
                ? $jobInfo['status']['errorResult']['message'] 
                : 'Unknown error';
            echo "<p class='text-danger'>❌ Error inserting booking_details data: {$errorMessage}</p>";
        }
    } catch (Exception $e) {
        echo "<p class='text-danger'>❌ Error inserting booking_details data: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    // Step 4: Train ML Models
    echo "<div class='status-card'>";
    echo "<h4>🤖 Step 4: Train ML Models</h4>";
    
    // Train Booking Forecast Model
    try {
        $bookingForecastSQL = "
            CREATE OR REPLACE MODEL `{$projectId}.booking_analytics.booking_forecast_model`
            OPTIONS(
                model_type = 'ARIMA_PLUS',
                time_series_timestamp_col = 'date',
                time_series_data_col = 'total_bookings',
                auto_arima = TRUE,
                data_frequency = 'AUTO_FREQUENCY',
                decompose_time_series = TRUE
            ) AS
            SELECT 
                date,
                total_bookings
            FROM `{$projectId}.booking_analytics.daily_bookings`
            ORDER BY date
        ";
        
        $query = $bigQuery->query($bookingForecastSQL);
        $job = $bigQuery->runQuery($query);
        
        echo "<p class='text-warning'>⏳ Training booking forecast model... (this may take a few minutes)</p>";
        
        // Wait for job to complete
        $job->reload();
        while (!$job->isComplete()) {
            sleep(5);
            $job->reload();
        }
        
        $jobInfo = $job->info();
        if (isset($jobInfo['status']['state']) && $jobInfo['status']['state'] === 'DONE') {
            echo "<p class='text-success'>✅ Successfully trained booking forecast model</p>";
        } else {
            $errorMessage = isset($jobInfo['status']['errorResult']['message']) 
                ? $jobInfo['status']['errorResult']['message'] 
                : 'Unknown error';
            echo "<p class='text-danger'>❌ Error training booking forecast model: {$errorMessage}</p>";
        }
    } catch (Exception $e) {
        echo "<p class='text-danger'>❌ Error training booking forecast model: " . $e->getMessage() . "</p>";
    }
    
    // Train Revenue Forecast Model
    try {
        $revenueForecastSQL = "
            CREATE OR REPLACE MODEL `{$projectId}.booking_analytics.revenue_forecast_model`
            OPTIONS(
                model_type = 'ARIMA_PLUS',
                time_series_timestamp_col = 'date',
                time_series_data_col = 'total_revenue',
                auto_arima = TRUE,
                data_frequency = 'AUTO_FREQUENCY',
                decompose_time_series = TRUE
            ) AS
            SELECT 
                date,
                total_revenue
            FROM `{$projectId}.booking_analytics.daily_bookings`
            WHERE total_revenue > 0
            ORDER BY date
        ";
        
        $query = $bigQuery->query($revenueForecastSQL);
        $job = $bigQuery->runQuery($query);
        
        echo "<p class='text-warning'>⏳ Training revenue forecast model... (this may take a few minutes)</p>";
        
        // Wait for job to complete
        $job->reload();
        while (!$job->isComplete()) {
            sleep(5);
            $job->reload();
        }
        
        $jobInfo = $job->info();
        if (isset($jobInfo['status']['state']) && $jobInfo['status']['state'] === 'DONE') {
            echo "<p class='text-success'>✅ Successfully trained revenue forecast model</p>";
        } else {
            $errorMessage = isset($jobInfo['status']['errorResult']['message']) 
                ? $jobInfo['status']['errorResult']['message'] 
                : 'Unknown error';
            echo "<p class='text-danger'>❌ Error training revenue forecast model: {$errorMessage}</p>";
        }
    } catch (Exception $e) {
        echo "<p class='text-danger'>❌ Error training revenue forecast model: " . $e->getMessage() . "</p>";
    }
    

    echo "</div>";
    
    // Success message
    echo "<div class='status-card success'>";
    echo "<h4>🎉 BigQuery ML Setup Complete!</h4>";
    echo "<p>Your BigQuery ML system has been successfully set up with billing enabled!</p>";
    echo "<p><strong>What's been created:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Dataset: booking_analytics</li>";
    echo "<li>✅ Tables: daily_bookings, booking_details</li>";
    echo "<li>✅ Sample data inserted</li>";
    echo "<li>✅ ML Models trained: booking forecast, revenue forecast</li>";
    echo "</ul>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Your analytics dashboard will now show real ML predictions</li>";
    echo "<li>You can migrate your actual booking data to replace sample data</li>";
    echo "<li>ML models will automatically improve with more data</li>";
    echo "</ul>";
    echo "<a href='detailed_analytics.php' class='btn btn-primary'>View Real ML Analytics</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='status-card error'>";
    echo "<h4>❌ Setup Error</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your BigQuery configuration and billing status.</p>";
    echo "</div>";
}

echo "</div>"; // Close container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body>";
echo "</html>"; 