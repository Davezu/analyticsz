# KingLang Analytics Integration Guide

## 📦 What You Need

To integrate this analytics system into your PHP application, you need:

### Required Files:

1. **analytics/** folder (entire folder)
2. **app/Classes/BigQueryHelper.php**
3. **composer.json** (for dependencies)
4. **Google Cloud credentials JSON file**

---

## 🚀 Quick Start Integration

### Step 1: Copy Files

```bash
# Copy analytics folder to your project
cp -r analytics/ /your-project/

# Copy BigQueryHelper class
mkdir -p /your-project/app/Classes/
cp app/Classes/BigQueryHelper.php /your-project/app/Classes/
```

### Step 2: Install Dependencies

```bash
cd /your-project
composer require google/cloud-bigquery
```

### Step 3: Configure Google Cloud

1. Create a Google Cloud Project
2. Enable BigQuery API
3. Create a service account
4. Download credentials JSON
5. Save as `storage/google-credentials.json`

### Step 4: Update Configuration

Edit `analytics/analytics.php`:

```php
// Update these with your values
define('BIGQUERY_PROJECT_ID', 'your-project-id');
define('BIGQUERY_DATASET_ID', 'your_dataset_name');
define('BIGQUERY_CREDENTIALS_PATH', __DIR__ . '/../storage/google-credentials.json');
```

### Step 5: Adapt Database Connection

Update the database connection in your files to match your system:

```php
// In analytics dashboard files, update this line:
require_once __DIR__ . '/../../config/database.php';

// To match YOUR database config location
```

### Step 6: Update Data Schema

Your MySQL database needs these tables:

- `bookings` table with columns: booking_id, destination, date_of_tour, number_of_days, balance, status, booked_at

If your schema is different, update:

- `analytics/setup/run_migration.php` (lines 50-80)
- Adjust the SQL query to match YOUR table structure

---

## 📊 Database Requirements

### MySQL Tables Needed:

```sql
-- Minimum required columns in your bookings table
bookings (
    booking_id INT PRIMARY KEY,
    destination VARCHAR(255),
    date_of_tour DATE,
    number_of_days INT,
    balance DECIMAL(10,2),
    status VARCHAR(50),
    booked_at DATETIME
)
```

---

## 🔧 Customization

### If Your Schema is Different:

1. **Open:** `analytics/setup/run_migration.php`
2. **Find:** Line 50 (SQL query)
3. **Update:** Column names to match your database

Example:

```php
// Original
$stmt = $pdo->query("SELECT booking_id, destination, date_of_tour...");

// If your columns are named differently:
$stmt = $pdo->query("SELECT id as booking_id, dest as destination...");
```

---

## 🎯 Integration Steps

### 1. Run Migration (First Time Only)

Visit in browser:

```
http://your-domain.com/analytics/setup/run_migration.php
```

This will:

- Export data from MySQL to BigQuery
- Create analytics tables
- Process historical data

### 2. Train ML Models (First Time Only)

Visit in browser:

```
http://your-domain.com/analytics/setup/setup_ml_with_billing.php
```

This will:

- Train booking forecast model
- Train revenue forecast model
- Takes 10-15 minutes

### 3. Access Analytics

Visit:

```
http://your-domain.com/analytics/
```

---

## 🔐 Security Considerations

1. **Protect credentials:**

   ```
   # Add to .gitignore
   storage/google-credentials.json
   analytics/analytics.php
   ```

2. **Restrict access:**

   ```php
   // Add authentication check at top of analytics files
   if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
       header('Location: /login');
       exit();
   }
   ```

3. **Use environment variables:**
   ```php
   // Instead of hardcoding in analytics.php
   define('BIGQUERY_PROJECT_ID', getenv('BIGQUERY_PROJECT_ID'));
   ```

---

## 📁 File Structure After Integration

```
your-project/
├── analytics/
│   ├── analytics.php
│   ├── index.php
│   ├── README.md
│   ├── dashboard/
│   ├── setup/
│   └── docs/
├── app/
│   └── Classes/
│       └── BigQueryHelper.php
├── storage/
│   └── google-credentials.json
├── config/
│   └── database.php
├── composer.json
└── vendor/
    └── google/cloud-bigquery/
```

---

## 🆘 Troubleshooting

### Issue: "BigQueryHelper class not found"

**Solution:** Check the path in dashboard files:

```php
require_once __DIR__ . '/../../app/Classes/BigQueryHelper.php';
```

### Issue: "Database connection failed"

**Solution:** Update database config path:

```php
require_once __DIR__ . '/../../config/database.php';
```

### Issue: "Google Cloud authentication failed"

**Solution:**

1. Check credentials file exists
2. Verify project ID is correct
3. Ensure BigQuery API is enabled

### Issue: "No data in analytics"

**Solution:**

1. Run migration first: `/analytics/setup/run_migration.php`
2. Ensure you have at least 30 bookings
3. Check BigQuery dataset in Google Cloud Console

---

## 🔄 Keeping Data in Sync

### Option 1: Manual Re-migration

Visit `/analytics/setup/run_migration.php` whenever you want to update data

### Option 2: Automated Sync (Recommended)

Create a cron job:

```bash
# Run daily at 2 AM
0 2 * * * php /path/to/your-project/analytics/setup/run_migration.php
```

### Option 3: Real-time Sync

Add to your booking creation code:

```php
// After saving booking to MySQL
require_once 'app/Classes/BigQueryHelper.php';
$bq = new BigQueryHelper();
$bq->syncBookingToBigQuery($bookingId);
```

---

## 📈 What You Get

- ✅ **Booking Forecasts:** Next 30 days predictions
- ✅ **Revenue Forecasts:** Revenue predictions with confidence intervals
- ✅ **Model Performance:** Accuracy metrics (MAE, RMSE, R²)
- ✅ **Daily Trends:** Historical patterns
- ✅ **ML-Powered:** Uses Google BigQuery ML (ARIMA_PLUS)

---

## 💰 Cost Considerations

**Google BigQuery Pricing:**

- First 10 GB of storage per month: **FREE**
- First 1 TB of queries per month: **FREE**
- ML model training: ~$0.05 per GB processed

**For a typical booking system:**

- Storage: < 1 GB (FREE)
- Queries: < 100 MB/month (FREE)
- ML training: ~$1-5/month

**Estimated Monthly Cost:** $0-5 (usually FREE for small systems)

---

## 🎓 Learning Resources

- [BigQuery Documentation](https://cloud.google.com/bigquery/docs)
- [BigQuery ML Guide](https://cloud.google.com/bigquery-ml/docs)
- [ARIMA_PLUS Model](https://cloud.google.com/bigquery-ml/docs/reference/standard-sql/bigqueryml-syntax-create-time-series)

---

## 📞 Support

If you encounter issues during integration:

1. Check `analytics/docs/` for detailed guides
2. Review error logs in browser console
3. Verify Google Cloud setup is complete
4. Ensure all file paths are correctly adjusted

---

## ✅ Integration Checklist

- [ ] Copy `analytics/` folder
- [ ] Copy `app/Classes/BigQueryHelper.php`
- [ ] Install Composer dependencies
- [ ] Set up Google Cloud project
- [ ] Enable BigQuery API
- [ ] Create service account & download credentials
- [ ] Update `analytics/analytics.php` with your config
- [ ] Adjust database connection paths
- [ ] Update data schema mapping (if needed)
- [ ] Run migration script
- [ ] Train ML models
- [ ] Access analytics dashboard
- [ ] Set up automated sync (optional)
- [ ] Add authentication/security
- [ ] Test predictions

---

**That's it! Your analytics system is now integrated!** 🎉
