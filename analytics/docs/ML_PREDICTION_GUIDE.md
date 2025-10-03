# 🤖 Machine Learning Prediction System - Complete Guide

## 📋 Table of Contents

1. [Overview](#overview)
2. [How Predictions Work](#how-predictions-work)
3. [Technology Stack](#technology-stack)
4. [The ARIMA_PLUS Algorithm](#the-arima_plus-algorithm)
5. [Code Explanation](#code-explanation)
6. [Implementation Guide](#implementation-guide)
7. [Understanding the Output](#understanding-the-output)

---

## 🎯 Overview

This analytics system uses **Google BigQuery ML** to predict future booking volumes and revenue based on historical data. It's like having a crystal ball that looks at your past bookings and tells you what to expect in the future!

### What Does It Predict?

- **Booking Forecast**: Number of bookings for the next 30 days
- **Revenue Forecast**: Expected revenue for the next 30 days
- **Confidence Ranges**: Upper and lower bounds for each prediction

---

## 🧠 How Predictions Work

### The 3-Tier Prediction System

The system tries three approaches in order:

```
1. FIRST: Use trained ML model (most accurate)
   ↓
2. IF MODEL NOT FOUND: Use recent historical data
   ↓
3. IF NO DATA: Show demo/mock data
```

**Example Flow:**

```php
try {
    // Try ML Model
    $predictions = ML.FORECAST(booking_forecast_model);
} catch {
    try {
        // Fallback to historical data
        $predictions = SELECT * FROM daily_bookings LIMIT 7;
    } catch {
        // Show mock data for demonstration
        $predictions = [mock_data];
    }
}
```

---

## 💻 Technology Stack

### Core Technologies

#### 1. **Google BigQuery**

- **What**: Cloud-based data warehouse
- **Why**: Handles massive datasets efficiently
- **Role**: Stores booking data and runs ML models
- **Website**: https://cloud.google.com/bigquery

#### 2. **BigQuery ML (BQML)**

- **What**: Machine learning inside BigQuery
- **Why**: No need for Python/TensorFlow expertise
- **Role**: Trains and runs prediction models
- **Advantage**: SQL-based, no external tools needed

#### 3. **PHP (Backend)**

- **What**: Server-side programming language
- **Version**: PHP 7.4+
- **Role**: Connects to BigQuery, fetches predictions
- **Libraries Used**:
  - `google/cloud-bigquery` (Composer package)

#### 4. **JavaScript (Frontend)**

- **What**: Client-side interactivity
- **Libraries**:
  - **Chart.js**: For beautiful prediction charts
  - **Bootstrap 5**: For responsive UI design

### Architecture Diagram

```
┌─────────────────┐
│   MySQL DB      │ ← Your booking data
│  (bookings)     │
└────────┬────────┘
         │ Data Export
         ↓
┌─────────────────┐
│  BigQuery       │
│  - daily_bookings
│  - booking_details
└────────┬────────┘
         │ Train Models
         ↓
┌─────────────────┐
│  ML Models      │
│  - booking_forecast_model
│  - revenue_forecast_model
└────────┬────────┘
         │ Generate Predictions
         ↓
┌─────────────────┐
│  PHP Dashboard  │ → Display predictions
│  (analytics/)   │
└─────────────────┘
```

---

## 📊 The ARIMA_PLUS Algorithm

### What is ARIMA?

**ARIMA** = **A**uto**R**egressive **I**ntegrated **M**oving **A**verage

Think of it as a smart way to predict the future by learning from the past!

### Components Explained

#### 1. **AR (Auto-Regressive)**

- **What it does**: Uses past values to predict future
- **Example**: "If we had 15 bookings yesterday, we'll likely have similar today"
- **Formula**: `Today = a × Yesterday + b × Day_Before + ...`

#### 2. **I (Integrated)**

- **What it does**: Handles trends in data
- **Example**: "Bookings are growing by 5% each month"
- **Why needed**: Makes non-stationary data stationary

#### 3. **MA (Moving Average)**

- **What it does**: Smooths out random noise
- **Example**: "Ignore that one-day spike from a special event"
- **Formula**: `Value = Average of recent prediction errors`

#### 4. **PLUS (Google's Enhancement)**

- **Auto-tuning**: Automatically finds best parameters
- **Handles seasonality**: Detects weekly/monthly patterns
- **Anomaly detection**: Ignores outliers
- **Holiday effects**: Adjusts for special dates

### Visual Example

```
Historical Data:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Jan 1:  12 bookings
Jan 2:  15 bookings  ← Pattern: Increasing
Jan 3:  18 bookings  ← Trend: +3 per day
Jan 4:  14 bookings  ← Seasonality: Drops mid-week
Jan 5:  16 bookings
Jan 6:  19 bookings
Jan 7:  22 bookings  ← Weekends are higher

ML Model Learns:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✓ Average: 16.5 bookings/day
✓ Growth trend: +1.5 per day
✓ Weekend spike: +30%
✓ Mid-week dip: -15%

Predictions:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Jan 8:  14 bookings (Mon, mid-week dip)
Jan 9:  15 bookings (Tue, slight recovery)
Jan 10: 17 bookings (Wed, normal)
Jan 11: 16 bookings (Thu, normal)
Jan 12: 18 bookings (Fri, weekend boost)
Jan 13: 24 bookings (Sat, peak)
Jan 14: 23 bookings (Sun, peak)
```

---

## 💻 Code Explanation

### 1. Booking Forecast Code

**File**: `app/Classes/BigQueryHelper.php`

```php
public function getBookingForecast(): array
{
    try {
        // SQL query to BigQuery ML
        $sql = "
            SELECT
                forecast_timestamp as forecast_date,      -- Future date
                forecast_value as predicted_bookings,      -- Predicted number
                prediction_interval_lower_bound as lower_bound,  -- Min expected
                prediction_interval_upper_bound as upper_bound   -- Max expected
            FROM ML.FORECAST(
                MODEL `{$this->projectId}.{$this->datasetId}.booking_forecast_model`,
                STRUCT(30 AS horizon, 0.8 AS confidence_level)
            )
            ORDER BY forecast_timestamp
        ";
        return $this->executeQuery($sql);
    } catch (Exception $e) {
        // Fallback logic here...
    }
}
```

**What each part does:**

```sql
ML.FORECAST(...)  -- Built-in BigQuery function for predictions
  MODEL booking_forecast_model  -- Our trained model
  STRUCT(
    30 AS horizon,              -- Predict next 30 days
    0.8 AS confidence_level     -- 80% confidence (80% of predictions will be accurate)
  )
```

### 2. Model Training Code

```php
public function trainBookingForecastModel(): bool
{
    $sql = "
        CREATE OR REPLACE MODEL booking_forecast_model
        OPTIONS(
            model_type = 'ARIMA_PLUS',              -- Algorithm
            time_series_timestamp_col = 'booking_date',  -- Date column
            time_series_data_col = 'total_bookings',     -- What to predict
            auto_arima = TRUE,                      -- Auto-find best parameters
            data_frequency = 'AUTO_FREQUENCY',      -- Detect daily/weekly patterns
            decompose_time_series = TRUE            -- Separate trend + seasonality
        ) AS
        SELECT
            booking_date,
            total_bookings
        FROM daily_bookings
        WHERE booking_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
        ORDER BY booking_date
    ";

    return $this->executeQuery($sql);
}
```

**Training Process:**

1. Takes last 90 days of booking data
2. Analyzes patterns, trends, seasonality
3. Creates a mathematical model
4. Stores model in BigQuery
5. Model is ready for predictions!

### 3. Dashboard Display Code

**File**: `analytics/dashboard/detailed_analytics.php`

```php
// Fetch predictions from BigQuery
$bookingForecast = $bigQueryHelper->getBookingForecast();
$revenueForecast = $bigQueryHelper->getRevenueForecast();

// Display in HTML table
foreach ($bookingForecast as $forecast) {
    echo "<tr>";
    echo "<td>" . $forecast['forecast_date'] . "</td>";
    echo "<td>" . $forecast['predicted_bookings'] . "</td>";
    echo "<td>" . $forecast['lower_bound'] . " - " . $forecast['upper_bound'] . "</td>";
    echo "</tr>";
}
```

### 4. Chart.js Visualization

```javascript
// Create beautiful prediction chart
const ctx = document.getElementById('bookingChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: dates, // ['2024-01-21', '2024-01-22', ...]
    datasets: [
      {
        label: 'Predicted Bookings',
        data: predictions, // [12, 15, 18, ...]
        borderColor: 'rgb(75, 192, 192)',
        tension: 0.1,
      },
    ],
  },
});
```

---

## 🚀 Implementation Guide

### Prerequisites

Before you start, you need:

1. ✅ **Google Cloud Account**

   - Sign up at: https://cloud.google.com
   - Free tier: $300 credit for 90 days

2. ✅ **BigQuery API Enabled**

   - Go to Google Cloud Console
   - Enable "BigQuery API"
   - Enable "BigQuery ML API"

3. ✅ **Service Account Credentials**

   - Create service account
   - Download JSON key file
   - Save as `storage/google-credentials.json`

4. ✅ **Composer Dependencies**

   ```bash
   composer require google/cloud-bigquery
   ```

5. ✅ **PHP 7.4+**
   - Check: `php -v`

### Step-by-Step Implementation

#### Step 1: Set Up BigQuery Project

1. Go to Google Cloud Console
2. Create new project or select existing
3. Note your Project ID (e.g., `my-booking-analytics`)

#### Step 2: Configure Credentials

1. Place your service account JSON in:

   ```
   storage/google-credentials.json
   ```

2. Update `analytics/analytics.php`:
   ```php
   define('BIGQUERY_PROJECT_ID', 'your-project-id');
   define('BIGQUERY_DATASET_ID', 'booking_analytics');
   define('BIGQUERY_CREDENTIALS_PATH', __DIR__ . '/../storage/google-credentials.json');
   ```

#### Step 3: Create BigQuery Tables

**Option A: Web Interface**

```
Visit: http://localhost:8000/analytics/setup/setup_analytics_tables.php
```

**Option B: PHP Script**

```php
$bigQuery = new BigQueryHelper();
// Tables are created automatically on first use
```

**Option C: Manual SQL**

```sql
CREATE TABLE booking_analytics.daily_bookings (
    date DATE,
    total_bookings INT64,
    total_revenue FLOAT64,
    confirmed_bookings INT64
);
```

#### Step 4: Migrate Data from MySQL to BigQuery

**Run the migration:**

```
Visit: http://localhost:8000/analytics/setup/run_migration.php
```

**What it does:**

1. Exports bookings from MySQL
2. Transforms data for analytics
3. Uploads to BigQuery
4. Creates aggregated tables

#### Step 5: Train ML Models

**Option A: Web Interface**

```
Visit: http://localhost:8000/analytics/setup/setup_ml_with_billing.php
```

**Option B: PHP Code**

```php
$bigQuery = new BigQueryHelper();

// Train booking forecast
$bigQuery->trainBookingForecastModel();

// Train revenue forecast
$bigQuery->trainRevenueForecastModel();
```

**Training time**: 3-10 minutes per model

#### Step 6: View Predictions

**Access your analytics:**

```
http://localhost:8000/analytics/
http://localhost:8000/analytics/dashboard/simple_analytics.php
http://localhost:8000/analytics/dashboard/detailed_analytics.php
```

---

## 📈 Understanding the Output

### Prediction Table Columns

| Column                 | Meaning                 | Example       |
| ---------------------- | ----------------------- | ------------- |
| **Date**               | Future date             | 2024-01-21    |
| **Predicted Bookings** | Most likely value       | 14.03         |
| **Range**              | 80% confidence interval | 12.45 - 15.62 |

### Interpreting the Range

```
Predicted: 14.03 bookings
Range: 12.45 - 15.62

Interpretation:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✓ Best estimate: 14 bookings
✓ 80% confident between 12-16 bookings
✓ 10% chance it's below 12
✓ 10% chance it's above 16

Planning:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• Optimistic plan: Prepare for 16 bookings
• Realistic plan: Expect 14 bookings
• Conservative plan: Plan for 12 bookings
```

### Accuracy Metrics

**Model Performance Table:**

```
Model Name          | Type       | Accuracy | Status
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Booking Forecast    | ARIMA_PLUS | 85.3%    | Trained
Revenue Forecast    | ARIMA_PLUS | 82.7%    | Trained
```

**What accuracy means:**

- **85%**: Model predictions are 85% accurate on average
- **Higher is better**: 80%+ is very good
- **Improving**: Accuracy increases with more data

---

## 🔧 Troubleshooting

### Common Issues

#### 1. "Model not found" Error

```
Solution: Train the models first
Visit: /analytics/setup/setup_ml_with_billing.php
```

#### 2. "Insufficient data" Error

```
Solution: You need at least 30 days of booking data
Add more historical data to MySQL database
```

#### 3. "Credentials not found" Error

```
Solution: Check your service account JSON file
Path: storage/google-credentials.json
```

#### 4. Strange/Wrong Predictions

```
Possible causes:
- Not enough training data (need 90+ days)
- Data quality issues (missing dates, outliers)
- Major business changes (new service, price change)

Solution: Re-train model with clean data
```

---

## 📊 Best Practices

### For Accurate Predictions

1. **Data Quality**

   - ✅ Complete: No missing dates
   - ✅ Consistent: Same format everywhere
   - ✅ Clean: Remove obvious errors

2. **Training Data**

   - ✅ Minimum: 30 days (model works)
   - ✅ Recommended: 90 days (better accuracy)
   - ✅ Ideal: 365 days (best accuracy)

3. **Regular Re-training**

   - Re-train monthly for best results
   - Re-train after major business changes
   - Re-train if predictions become inaccurate

4. **Monitoring**
   - Compare predictions vs actual results
   - Track model accuracy over time
   - Adjust if accuracy drops below 70%

---

## 🎯 Real-World Applications

### Use Case 1: Staffing

```
Prediction: 20 bookings on Saturday

Action:
- Schedule 4 drivers (normal = 2)
- Add extra customer service staff
- Prepare more buses
```

### Use Case 2: Inventory

```
Prediction: ₱50,000 revenue next week

Action:
- Ensure fuel supply
- Schedule maintenance off-peak
- Order supplies in advance
```

### Use Case 3: Marketing

```
Prediction: Low bookings next Tuesday

Action:
- Run promotional campaign
- Offer Tuesday discount
- Send email to past customers
```

---

## 📚 Further Reading

### Documentation

- **BigQuery ML Docs**: https://cloud.google.com/bigquery-ml/docs
- **ARIMA_PLUS Guide**: https://cloud.google.com/bigquery-ml/docs/reference/standard-sql/bigqueryml-syntax-create-time-series
- **Time Series Forecasting**: https://cloud.google.com/bigquery-ml/docs/arima-single-time-series-forecasting-tutorial

### Learning Resources

- **BigQuery ML Tutorial**: https://cloud.google.com/bigquery-ml/docs/tutorials
- **Machine Learning Basics**: https://developers.google.com/machine-learning/crash-course
- **Time Series Analysis**: Khan Academy, Coursera

---

## 🤝 Support

Need help? Check:

1. `analytics/docs/ANALYTICS_README.md` - General analytics guide
2. `README.md` - Main project documentation
3. Google Cloud support forums

---

**Last Updated**: 2024
**Version**: 1.0
**Author**: KingLang Transport Analytics Team
