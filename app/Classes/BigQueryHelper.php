<?php

namespace App\Classes;

use Google\Cloud\BigQuery\BigQueryClient;
use Exception;

class BigQueryHelper
{
    private $bigQuery;
    private $projectId;
    private $datasetId;
    private $credentialsPath;

    public function __construct()
    {
        // Load configuration
        require_once __DIR__ . '/../../analytics/analytics.php';
        
        $this->projectId = BIGQUERY_PROJECT_ID;
        $this->datasetId = BIGQUERY_DATASET_ID;
        $this->credentialsPath = BIGQUERY_CREDENTIALS_PATH;

        // Initialize BigQuery client
        if (file_exists($this->credentialsPath)) {
            $this->bigQuery = new BigQueryClient([
                'projectId' => $this->projectId,
                'keyFilePath' => $this->credentialsPath
            ]);
        } else {
            throw new Exception("BigQuery credentials file not found at: {$this->credentialsPath}");
        }
    }

    /**
     * Get BigQuery client instance
     */
    public function getBigQueryClient()
    {
        return $this->bigQuery;
    }

    /**
     * Get project ID
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Get dataset ID
     */
    public function getDatasetId()
    {
        return $this->datasetId;
    }

    /**
     * Execute a BigQuery SQL query
     */
    public function executeQuery($sql, $parameters = [])
    {
        $queryJobConfig = $this->bigQuery->query($sql);
        
        if (!empty($parameters)) {
            $queryJobConfig->parameters($parameters);
        }

        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        $rows = [];
        foreach ($queryResults as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Train booking forecast model
     */
    public function trainBookingForecastModel()
    {
        $sql = "
        CREATE OR REPLACE MODEL `{$this->projectId}.{$this->datasetId}.booking_forecast_model`
        OPTIONS(
            model_type='ARIMA_PLUS',
            time_series_timestamp_col='date',
            time_series_data_col='total_bookings',
            auto_arima=TRUE,
            data_frequency='AUTO_FREQUENCY',
            decompose_time_series=TRUE
        ) AS
        SELECT
            date,
            total_bookings
        FROM `{$this->projectId}.{$this->datasetId}.daily_bookings`
        WHERE date >= DATE_SUB(CURRENT_DATE(), INTERVAL 365 DAY)
        ORDER BY date ASC
        ";

        try {
            $this->executeQuery($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error training booking forecast model: " . $e->getMessage());
        }
    }

    /**
     * Train revenue forecast model
     */
    public function trainRevenueForecastModel()
    {
        $sql = "
        CREATE OR REPLACE MODEL `{$this->projectId}.{$this->datasetId}.revenue_forecast_model`
        OPTIONS(
            model_type='ARIMA_PLUS',
            time_series_timestamp_col='date',
            time_series_data_col='total_revenue',
            auto_arima=TRUE,
            data_frequency='AUTO_FREQUENCY',
            decompose_time_series=TRUE
        ) AS
        SELECT
            date,
            total_revenue
        FROM `{$this->projectId}.{$this->datasetId}.daily_bookings`
        WHERE date >= DATE_SUB(CURRENT_DATE(), INTERVAL 365 DAY)
        ORDER BY date ASC
        ";

        try {
            $this->executeQuery($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error training revenue forecast model: " . $e->getMessage());
        }
    }

    /**
     * Get booking forecast
     */
    public function getBookingForecast($days = 30)
    {
        $sql = "
        SELECT
            forecast_timestamp AS date,
            forecast_value AS predicted_bookings,
            standard_error,
            confidence_level,
            prediction_interval_lower_bound AS lower_bound,
            prediction_interval_upper_bound AS upper_bound
        FROM ML.FORECAST(
            MODEL `{$this->projectId}.{$this->datasetId}.booking_forecast_model`,
            STRUCT({$days} AS horizon, 0.95 AS confidence_level)
        )
        ORDER BY forecast_timestamp ASC
        ";

        return $this->executeQuery($sql);
    }

    /**
     * Get revenue forecast
     */
    public function getRevenueForecast($days = 30)
    {
        $sql = "
        SELECT
            forecast_timestamp AS date,
            forecast_value AS predicted_revenue,
            standard_error,
            confidence_level,
            prediction_interval_lower_bound AS lower_bound,
            prediction_interval_upper_bound AS upper_bound
        FROM ML.FORECAST(
            MODEL `{$this->projectId}.{$this->datasetId}.revenue_forecast_model`,
            STRUCT({$days} AS horizon, 0.95 AS confidence_level)
        )
        ORDER BY forecast_timestamp ASC
        ";

        return $this->executeQuery($sql);
    }

    /**
     * Get model evaluation metrics
     */
    public function getModelMetrics($modelName)
    {
        $sql = "
        SELECT *
        FROM ML.EVALUATE(
            MODEL `{$this->projectId}.{$this->datasetId}.{$modelName}`
        )
        ";

        return $this->executeQuery($sql);
    }

    /**
     * Insert booking data to BigQuery
     */
    public function insertBooking($bookingData)
    {
        $dataset = $this->bigQuery->dataset($this->datasetId);
        $table = $dataset->table('bookings');
        
        $insertResponse = $table->insertRows([
            ['data' => $bookingData]
        ]);

        if ($insertResponse->isSuccessful()) {
            return true;
        } else {
            foreach ($insertResponse->failedRows() as $row) {
                throw new Exception("Insert failed: " . json_encode($row['errors']));
            }
        }
    }

    /**
     * Sync booking to BigQuery
     */
    public function syncBookingToBigQuery($bookingId)
    {
        // This method should fetch booking from MySQL and insert to BigQuery
        // Implementation depends on your database structure
        throw new Exception("syncBookingToBigQuery method needs to be implemented based on your database schema");
    }
}

