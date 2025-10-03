-- =====================================================
-- BIGQUERY ML ANALYTICS FOR KINGLANG BOOKING SYSTEM
-- =====================================================

-- Step 1: Create datasets and tables in BigQuery
-- First, create a dataset for your booking analytics
-- CREATE DATASET IF NOT EXISTS `your-project-id.booking_analytics`;

-- Step 2: Create daily_bookings table from your existing data
-- This query aggregates your bookings data into daily summaries
CREATE OR REPLACE TABLE `your-project-id.booking_analytics.daily_bookings` AS
SELECT 
  DATE(booked_at) as booking_date,
  COUNT(*) as total_bookings,
  SUM(COALESCE(bc.total_cost, 0)) as total_revenue,
  AVG(COALESCE(bc.total_cost, 0)) as avg_booking_value,
  COUNT(CASE WHEN status = 'Canceled' THEN 1 END) as canceled_bookings,
  COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_bookings
FROM `your-project-id.booking_analytics.bookings` b
LEFT JOIN `your-project-id.booking_analytics.booking_costs` bc 
  ON b.booking_id = bc.booking_id
WHERE booked_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 365 DAY)
GROUP BY DATE(booked_at)
ORDER BY booking_date;

-- Step 3: Create booking_details table for cancellation prediction
CREATE OR REPLACE TABLE `your-project-id.booking_analytics.booking_details` AS
SELECT 
  b.booking_id,
  DATE(b.booked_at) as booking_date,
  b.destination,
  b.pickup_point,
  b.number_of_days,
  b.number_of_buses,
  COALESCE(bc.total_cost, 0) as price,
  CASE WHEN b.status = 'Canceled' THEN 1 ELSE 0 END as cancellation_flag,
  EXTRACT(DAYOFWEEK FROM b.booked_at) as day_of_week,
  EXTRACT(MONTH FROM b.booked_at) as month,
  EXTRACT(YEAR FROM b.booked_at) as year,
  CASE 
    WHEN b.number_of_days <= 1 THEN 'Day Trip'
    WHEN b.number_of_days <= 3 THEN 'Short Trip'
    WHEN b.number_of_days <= 7 THEN 'Week Trip'
    ELSE 'Long Trip'
  END as trip_duration_category,
  CASE 
    WHEN COALESCE(bc.total_cost, 0) <= 5000 THEN 'Low Price'
    WHEN COALESCE(bc.total_cost, 0) <= 15000 THEN 'Medium Price'
    ELSE 'High Price'
  END as price_category
FROM `your-project-id.booking_analytics.bookings` b
LEFT JOIN `your-project-id.booking_analytics.booking_costs` bc 
  ON b.booking_id = bc.booking_id
WHERE booked_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 365 DAY);

-- =====================================================
-- 1. FORECAST AVERAGE BOOKINGS USING ARIMA_PLUS
-- =====================================================

-- Train ARIMA_PLUS model for booking forecasts
CREATE OR REPLACE MODEL `your-project-id.booking_analytics.booking_forecast_model`
OPTIONS(
  model_type = 'ARIMA_PLUS',
  time_series_timestamp_col = 'booking_date',
  time_series_data_col = 'total_bookings',
  auto_arima = TRUE,
  data_frequency = 'AUTO_FREQUENCY',
  decompose_time_series = TRUE
) AS
SELECT 
  booking_date,
  total_bookings
FROM `your-project-id.booking_analytics.daily_bookings`
WHERE booking_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
ORDER BY booking_date;

-- Forecast bookings for next 30 days
SELECT 
  forecast_timestamp as forecast_date,
  forecast_value as predicted_bookings,
  prediction_interval_lower_bound as lower_bound,
  prediction_interval_upper_bound as upper_bound
FROM ML.FORECAST(
  MODEL `your-project-id.booking_analytics.booking_forecast_model`,
  STRUCT(30 AS horizon, 0.8 AS confidence_level)
)
ORDER BY forecast_timestamp;

-- =====================================================
-- 2. FORECAST REVENUE USING ARIMA_PLUS
-- =====================================================

-- Train ARIMA_PLUS model for revenue forecasts
CREATE OR REPLACE MODEL `your-project-id.booking_analytics.revenue_forecast_model`
OPTIONS(
  model_type = 'ARIMA_PLUS',
  time_series_timestamp_col = 'booking_date',
  time_series_data_col = 'total_revenue',
  auto_arima = TRUE,
  data_frequency = 'AUTO_FREQUENCY',
  decompose_time_series = TRUE
) AS
SELECT 
  booking_date,
  total_revenue
FROM `your-project-id.booking_analytics.daily_bookings`
WHERE booking_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
  AND total_revenue > 0
ORDER BY booking_date;

-- Forecast revenue for next 30 days
SELECT 
  forecast_timestamp as forecast_date,
  forecast_value as predicted_revenue,
  prediction_interval_lower_bound as lower_bound,
  prediction_interval_upper_bound as upper_bound
FROM ML.FORECAST(
  MODEL `your-project-id.booking_analytics.revenue_forecast_model`,
  STRUCT(30 AS horizon, 0.8 AS confidence_level)
)
ORDER BY forecast_timestamp;

-- =====================================================
-- 3. CANCELLATION ANALYTICS (DESCRIPTIVE ONLY)
-- =====================================================

-- Get cancellation rates by trip duration (descriptive analytics)
SELECT 
  trip_duration_category,
  COUNT(*) as total_bookings,
  SUM(cancellation_flag) as canceled_bookings,
  ROUND(SUM(cancellation_flag) * 100.0 / COUNT(*), 2) as cancellation_rate
FROM `your-project-id.booking_analytics.booking_details`
GROUP BY trip_duration_category
ORDER BY cancellation_rate DESC;

-- =====================================================
-- 4. ADDITIONAL ANALYTICS QUERIES
-- =====================================================

-- Daily booking trends with moving averages
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
  ) as revenue_7day_avg,
  LAG(total_bookings, 1) OVER (ORDER BY booking_date) as prev_day_bookings,
  LAG(total_revenue, 1) OVER (ORDER BY booking_date) as prev_day_revenue
FROM `your-project-id.booking_analytics.daily_bookings`
ORDER BY booking_date DESC
LIMIT 30;



-- Revenue analysis by price category
SELECT 
  price_category,
  COUNT(*) as total_bookings,
  AVG(price) as avg_price,
  SUM(price) as total_revenue,
  SUM(cancellation_flag) as canceled_bookings,
  ROUND(SUM(cancellation_flag) * 100.0 / COUNT(*), 2) as cancellation_rate
FROM `your-project-id.booking_analytics.booking_details`
GROUP BY price_category
ORDER BY total_revenue DESC;

-- Weekly booking patterns
SELECT 
  day_of_week,
  COUNT(*) as total_bookings,
  AVG(price) as avg_price,
  SUM(cancellation_flag) as canceled_bookings,
  ROUND(SUM(cancellation_flag) * 100.0 / COUNT(*), 2) as cancellation_rate
FROM `your-project-id.booking_analytics.booking_details`
GROUP BY day_of_week
ORDER BY day_of_week;

-- =====================================================
-- 5. MODEL MAINTENANCE QUERIES
-- =====================================================

-- Check model performance over time
SELECT 
  model_name,
  creation_time,
  last_modified_time,
  model_type,
  training_runs
FROM `your-project-id.booking_analytics.INFORMATION_SCHEMA.ML_MODELS`
ORDER BY creation_time DESC;

-- Get model training statistics
SELECT 
  *
FROM ML.TRAINING_INFO(
  MODEL `your-project-id.booking_analytics.cancellation_prediction_model`
);

-- =====================================================
-- 6. DATA QUALITY CHECKS
-- =====================================================

-- Check for missing data
SELECT 
  COUNT(*) as total_records,
  COUNTIF(total_bookings IS NULL) as missing_bookings,
  COUNTIF(total_revenue IS NULL) as missing_revenue,
  COUNTIF(booking_date IS NULL) as missing_dates
FROM `your-project-id.booking_analytics.daily_bookings`;

-- Check data distribution
SELECT 
  MIN(booking_date) as earliest_date,
  MAX(booking_date) as latest_date,
  COUNT(DISTINCT booking_date) as total_days,
  AVG(total_bookings) as avg_daily_bookings,
  STDDEV(total_bookings) as stddev_bookings
FROM `your-project-id.booking_analytics.daily_bookings`; 