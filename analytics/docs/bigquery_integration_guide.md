# BigQuery ML Integration Guide for KingLang Booking System

## Overview

This guide provides step-by-step instructions for integrating BigQuery ML analytics into your PHP booking system to forecast bookings, revenue, and predict cancellations.

## Prerequisites

### 1. Google Cloud Setup

- Create a Google Cloud Project
- Enable BigQuery API
- Enable BigQuery ML API
- Create a service account with BigQuery permissions
- Download the service account JSON key file

### 2. PHP Dependencies

Install the Google Cloud BigQuery library:

```bash
composer require google/cloud-bigquery
```

## Installation Steps

### Step 1: Install Google Cloud SDK

```bash
# For Windows (using PowerShell)
# Download from: https://cloud.google.com/sdk/docs/install

# For Linux/Mac
curl https://sdk.cloud.google.com | bash
exec -l $SHELL
gcloud init
```

### Step 2: Set up Authentication

```bash
# Set your project ID
gcloud config set project YOUR_PROJECT_ID

# Authenticate with service account
gcloud auth activate-service-account --key-file=path/to/service-account-key.json
```

### Step 3: Create BigQuery Dataset

```sql
-- Run this in BigQuery Console
CREATE DATASET IF NOT EXISTS `your-project-id.booking_analytics`;
```

## PHP Integration Classes

### 1. BigQuery Connection Class

Create `app/Classes/BigQueryAnalytics.php`:

```php
<?php

namespace App\Classes;

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\QueryResults;
use Exception;

class BigQueryAnalytics
{
    private $bigQuery;
    private $projectId;
    private $datasetId;

    public function __construct()
    {
        $this->projectId = env('GOOGLE_CLOUD_PROJECT_ID');
        $this->datasetId = 'booking_analytics';

        $this->bigQuery = new BigQueryClient([
            'projectId' => $this->projectId,
            'keyFilePath' => storage_path('app/google-credentials.json')
        ]);
    }

    /**
     * Execute a BigQuery SQL query
     */
    public function executeQuery(string $sql): array
    {
        try {
            $query = $this->bigQuery->query($sql);
            $queryResults = $this->bigQuery->runQuery($query);

            return $this->formatResults($queryResults);
        } catch (Exception $e) {
            throw new Exception("BigQuery Error: " . $e->getMessage());
        }
    }

    /**
     * Format BigQuery results into array
     */
    private function formatResults(QueryResults $results): array
    {
        $data = [];
        foreach ($results as $row) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Get booking forecasts for next 30 days
     */
    public function getBookingForecast(): array
    {
        $sql = "
            SELECT
                forecast_timestamp as forecast_date,
                forecast_value as predicted_bookings,
                prediction_interval_lower_bound as lower_bound,
                prediction_interval_upper_bound as upper_bound
            FROM ML.FORECAST(
                MODEL `{$this->projectId}.{$this->datasetId}.booking_forecast_model`,
                STRUCT(30 AS horizon, 0.8 AS confidence_level)
            )
            ORDER BY forecast_timestamp
        ";

        return $this->executeQuery($sql);
    }

    /**
     * Get revenue forecasts for next 30 days
     */
    public function getRevenueForecast(): array
    {
        $sql = "
            SELECT
                forecast_timestamp as forecast_date,
                forecast_value as predicted_revenue,
                prediction_interval_lower_bound as lower_bound,
                prediction_interval_upper_bound as upper_bound
            FROM ML.FORECAST(
                MODEL `{$this->projectId}.{$this->datasetId}.revenue_forecast_model`,
                STRUCT(30 AS horizon, 0.8 AS confidence_level)
            )
            ORDER BY forecast_timestamp
        ";

        return $this->executeQuery($sql);
    }

    /**
     * Get cancellation predictions for recent bookings
     */
    public function getCancellationPredictions(): array
    {
        $sql = "
            SELECT
                booking_id,
                predicted_cancellation_flag,
                predicted_cancellation_flag_probs.prob as cancellation_probability,
                number_of_days,
                number_of_buses,
                price,
                trip_duration_category,
                price_category
            FROM ML.PREDICT(
                MODEL `{$this->projectId}.{$this->datasetId}.cancellation_prediction_model`,
                TABLE `{$this->projectId}.{$this->datasetId}.booking_details`
            )
            WHERE booking_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            ORDER BY cancellation_probability DESC
        ";

        return $this->executeQuery($sql);
    }

    /**
     * Get model performance metrics
     */
    public function getModelPerformance(): array
    {
        $sql = "
            SELECT
                *
            FROM ML.EVALUATE(
                MODEL `{$this->projectId}.{$this->datasetId}.cancellation_prediction_model`,
                TABLE `{$this->projectId}.{$this->datasetId}.booking_details`,
                STRUCT(TRUE AS perform_aggregation)
            )
        ";

        return $this->executeQuery($sql);
    }

    /**
     * Get daily booking trends
     */
    public function getDailyTrends(): array
    {
        $sql = "
            SELECT
                booking_date,
                total_bookings,
                total_revenue,
                AVG(total_bookings) OVER (
                    ORDER BY booking_date
                    ROWS BETWEEN 6 PRECEDING AND CURRENT ROW
                ) as booking_7day_avg,
                AVG(total_revenue) OVER (
                    ORDER BY booking_date
                    ROWS BETWEEN 6 PRECEDING AND CURRENT ROW
                ) as revenue_7day_avg
            FROM `{$this->projectId}.{$this->datasetId}.daily_bookings`
            ORDER BY booking_date DESC
            LIMIT 30
        ";

        return $this->executeQuery($sql);
    }

    /**
     * Get cancellation rates by category
     */
    public function getCancellationRates(): array
    {
        $sql = "
            SELECT
                trip_duration_category,
                COUNT(*) as total_bookings,
                SUM(cancellation_flag) as canceled_bookings,
                ROUND(SUM(cancellation_flag) * 100.0 / COUNT(*), 2) as cancellation_rate
            FROM `{$this->projectId}.{$this->datasetId}.booking_details`
            GROUP BY trip_duration_category
            ORDER BY cancellation_rate DESC
        ";

        return $this->executeQuery($sql);
    }
}
```

### 2. Analytics Controller

Create `app/Controllers/AnalyticsController.php`:

```php
<?php

namespace App\Controllers;

use App\Classes\BigQueryAnalytics;
use Exception;

class AnalyticsController
{
    private $bigQueryAnalytics;

    public function __construct()
    {
        $this->bigQueryAnalytics = new BigQueryAnalytics();
    }

    /**
     * Dashboard with all analytics
     */
    public function dashboard()
    {
        try {
            $data = [
                'booking_forecast' => $this->bigQueryAnalytics->getBookingForecast(),
                'revenue_forecast' => $this->bigQueryAnalytics->getRevenueForecast(),
                'cancellation_predictions' => $this->bigQueryAnalytics->getCancellationPredictions(),
                'model_performance' => $this->bigQueryAnalytics->getModelPerformance(),
                'daily_trends' => $this->bigQueryAnalytics->getDailyTrends(),
                'cancellation_rates' => $this->bigQueryAnalytics->getCancellationRates()
            ];

            return $this->renderView('analytics/dashboard', $data);
        } catch (Exception $e) {
            return $this->renderView('analytics/error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * API endpoint for booking forecast
     */
    public function getBookingForecast()
    {
        try {
            $forecast = $this->bigQueryAnalytics->getBookingForecast();
            return json_encode(['success' => true, 'data' => $forecast]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API endpoint for revenue forecast
     */
    public function getRevenueForecast()
    {
        try {
            $forecast = $this->bigQueryAnalytics->getRevenueForecast();
            return json_encode(['success' => true, 'data' => $forecast]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API endpoint for cancellation predictions
     */
    public function getCancellationPredictions()
    {
        try {
            $predictions = $this->bigQueryAnalytics->getCancellationPredictions();
            return json_encode(['success' => true, 'data' => $predictions]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function renderView($view, $data = [])
    {
        // Implement your view rendering logic here
        extract($data);
        include "views/{$view}.php";
    }
}
```

### 3. Dashboard View

Create `views/analytics/dashboard.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .metric-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .metric-value {
            font-size: 2em;
            font-weight: bold;
            color: #2563eb;
        }
        .metric-label {
            color: #6b7280;
            margin-top: 5px;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Booking Analytics Dashboard</h1>

        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value" id="avgBookings">-</div>
                <div class="metric-label">Avg Daily Bookings</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" id="avgRevenue">-</div>
                <div class="metric-label">Avg Daily Revenue</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" id="cancellationRate">-</div>
                <div class="metric-label">Cancellation Rate</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" id="modelAccuracy">-</div>
                <div class="metric-label">Model Accuracy</div>
            </div>
        </div>

        <!-- Booking Forecast Chart -->
        <div class="chart-container">
            <h2>Booking Forecast (Next 30 Days)</h2>
            <canvas id="bookingForecastChart"></canvas>
        </div>

        <!-- Revenue Forecast Chart -->
        <div class="chart-container">
            <h2>Revenue Forecast (Next 30 Days)</h2>
            <canvas id="revenueForecastChart"></canvas>
        </div>

        <!-- Daily Trends Chart -->
        <div class="chart-container">
            <h2>Daily Booking Trends</h2>
            <canvas id="dailyTrendsChart"></canvas>
        </div>

        <!-- Cancellation Predictions Table -->
        <div class="chart-container">
            <h2>High-Risk Cancellation Predictions</h2>
            <div id="cancellationTable"></div>
        </div>
    </div>

    <script>
        // Load analytics data
        async function loadAnalytics() {
            try {
                // Load booking forecast
                const bookingResponse = await axios.get('/analytics/booking-forecast');
                if (bookingResponse.data.success) {
                    createBookingForecastChart(bookingResponse.data.data);
                    updateMetrics(bookingResponse.data.data);
                }

                // Load revenue forecast
                const revenueResponse = await axios.get('/analytics/revenue-forecast');
                if (revenueResponse.data.success) {
                    createRevenueForecastChart(revenueResponse.data.data);
                }

                // Load cancellation predictions
                const cancellationResponse = await axios.get('/analytics/cancellation-predictions');
                if (cancellationResponse.data.success) {
                    createCancellationTable(cancellationResponse.data.data);
                }

            } catch (error) {
                console.error('Error loading analytics:', error);
                document.body.innerHTML = '<div class="loading">Error loading analytics data</div>';
            }
        }

        // Create booking forecast chart
        function createBookingForecastChart(data) {
            const ctx = document.getElementById('bookingForecastChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => new Date(item.forecast_date).toLocaleDateString()),
                    datasets: [{
                        label: 'Predicted Bookings',
                        data: data.map(item => item.predicted_bookings),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Create revenue forecast chart
        function createRevenueForecastChart(data) {
            const ctx = document.getElementById('revenueForecastChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => new Date(item.forecast_date).toLocaleDateString()),
                    datasets: [{
                        label: 'Predicted Revenue',
                        data: data.map(item => item.predicted_revenue),
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Create cancellation predictions table
        function createCancellationTable(data) {
            const highRiskBookings = data.filter(item => item.cancellation_probability > 0.7);

            let tableHTML = `
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f3f4f6;">
                            <th style="padding: 12px; text-align: left; border: 1px solid #d1d5db;">Booking ID</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #d1d5db;">Cancellation Probability</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #d1d5db;">Trip Duration</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #d1d5db;">Price Category</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #d1d5db;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            highRiskBookings.slice(0, 10).forEach(booking => {
                const probability = (booking.cancellation_probability * 100).toFixed(1);
                const color = booking.cancellation_probability > 0.9 ? '#dc2626' :
                             booking.cancellation_probability > 0.8 ? '#ea580c' : '#d97706';

                tableHTML += `
                    <tr>
                        <td style="padding: 12px; border: 1px solid #d1d5db;">${booking.booking_id}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db; color: ${color}; font-weight: bold;">
                            ${probability}%
                        </td>
                        <td style="padding: 12px; border: 1px solid #d1d5db;">${booking.trip_duration_category}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db;">${booking.price_category}</td>
                        <td style="padding: 12px; border: 1px solid #d1d5db;">₱${booking.price.toLocaleString()}</td>
                    </tr>
                `;
            });

            tableHTML += '</tbody></table>';
            document.getElementById('cancellationTable').innerHTML = tableHTML;
        }

        // Update key metrics
        function updateMetrics(bookingData) {
            const avgBookings = bookingData.reduce((sum, item) => sum + item.predicted_bookings, 0) / bookingData.length;
            document.getElementById('avgBookings').textContent = Math.round(avgBookings);
        }

        // Load analytics when page loads
        document.addEventListener('DOMContentLoaded', loadAnalytics);
    </script>
</body>
</html>
```

## Configuration

### 1. Environment Variables

Add to your `.env` file:

```
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_CREDENTIALS_PATH=storage/app/google-credentials.json
```

### 2. Routes

Add these routes to your routing file:

```php
// Analytics routes
$router->get('/analytics/dashboard', 'AnalyticsController@dashboard');
$router->get('/analytics/booking-forecast', 'AnalyticsController@getBookingForecast');
$router->get('/analytics/revenue-forecast', 'AnalyticsController@getRevenueForecast');
$router->get('/analytics/cancellation-predictions', 'AnalyticsController@getCancellationPredictions');
```

## Data Migration Steps

### 1. Export Data from MySQL

```bash
# Export bookings data
mysqldump -u username -p kinglang_booking bookings booking_costs > booking_data.sql

# Or use a PHP script to export data
```

### 2. Import to BigQuery

```bash
# Create tables in BigQuery
bq mk --table your-project-id:booking_analytics.bookings booking_id:INTEGER,destination:STRING,pickup_point:STRING,date_of_tour:DATE,end_of_tour:DATE,number_of_days:INTEGER,number_of_buses:INTEGER,balance:FLOAT,status:STRING,payment_status:STRING,user_id:INTEGER,booked_at:TIMESTAMP

bq mk --table your-project-id:booking_analytics.booking_costs id:INTEGER,total_cost:FLOAT,base_rate:FLOAT,total_distance:FLOAT,booking_id:INTEGER,diesel_price:FLOAT,diesel_cost:FLOAT,base_cost:FLOAT,discount:FLOAT,discount_type:STRING,discount_amount:FLOAT,gross_price:FLOAT

# Load data
bq load --source_format=CSV your-project-id:booking_analytics.bookings booking_data.csv
```

## Model Training Schedule

### 1. Automated Retraining

Create a cron job to retrain models weekly:

```bash
# Add to crontab
0 2 * * 0 /usr/bin/php /path/to/your/project/retrain_models.php
```

### 2. Retraining Script

Create `retrain_models.php`:

```php
<?php
require_once 'vendor/autoload.php';

use App\Classes\BigQueryAnalytics;

$analytics = new BigQueryAnalytics();

// Retrain booking forecast model
$analytics->executeQuery("
    CREATE OR REPLACE MODEL `your-project-id.booking_analytics.booking_forecast_model`
    OPTIONS(
        model_type = 'ARIMA_PLUS',
        time_series_timestamp_col = 'booking_date',
        time_series_data_col = 'total_bookings',
        auto_arima = TRUE
    ) AS
    SELECT booking_date, total_bookings
    FROM `your-project-id.booking_analytics.daily_bookings`
    WHERE booking_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
    ORDER BY booking_date
");

// Retrain revenue forecast model
$analytics->executeQuery("
    CREATE OR REPLACE MODEL `your-project-id.booking_analytics.revenue_forecast_model`
    OPTIONS(
        model_type = 'ARIMA_PLUS',
        time_series_timestamp_col = 'booking_date',
        time_series_data_col = 'total_revenue',
        auto_arima = TRUE
    ) AS
    SELECT booking_date, total_revenue
    FROM `your-project-id.booking_analytics.daily_bookings`
    WHERE booking_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
    AND total_revenue > 0
    ORDER BY booking_date
");

// Retrain cancellation prediction model
$analytics->executeQuery("
    CREATE OR REPLACE MODEL `your-project-id.booking_analytics.cancellation_prediction_model`
    OPTIONS(
        model_type = 'LOGISTIC_REG',
        input_label_cols = ['cancellation_flag'],
        auto_class_weights = TRUE
    ) AS
    SELECT
        cancellation_flag,
        number_of_days,
        number_of_buses,
        price,
        day_of_week,
        month,
        trip_duration_category,
        price_category
    FROM `your-project-id.booking_analytics.booking_details`
    WHERE booking_date < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
");

echo "Models retrained successfully at " . date('Y-m-d H:i:s') . "\n";
```

## Troubleshooting

### Common Issues:

1. **Authentication Errors**

   - Ensure service account has BigQuery permissions
   - Check key file path and permissions
   - Verify project ID is correct

2. **Model Training Failures**

   - Check data quality and completeness
   - Ensure sufficient historical data (at least 90 days)
   - Verify column names match exactly

3. **Performance Issues**
   - Use appropriate data filters
   - Consider caching frequently accessed results
   - Monitor BigQuery usage and costs

### Monitoring:

- Set up BigQuery usage alerts
- Monitor model accuracy over time
- Track API call costs
- Set up error logging for failed queries

## Cost Optimization

1. **Query Optimization**

   - Use appropriate date filters
   - Limit result sets
   - Cache results when possible

2. **Storage Optimization**

   - Archive old data
   - Use appropriate data types
   - Compress data when possible

3. **Model Management**
   - Retrain models only when necessary
   - Monitor model performance
   - Archive unused models

This integration provides a complete analytics solution for your booking system with forecasting, prediction, and visualization capabilities.
