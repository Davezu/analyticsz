# Analytics Setup Instructions

## 📋 What You Just Got

I've created the missing files needed for your BigQuery ML Analytics system:

- ✅ `composer.json` - PHP dependencies configuration
- ✅ `app/Classes/BigQueryHelper.php` - Main BigQuery integration class
- ✅ `config/database.php` - MySQL database configuration
- ✅ `storage/` folder - For Google credentials
- ✅ `.gitignore` - To protect sensitive files

## 🚀 Next Steps

### 1. Install Composer (if not installed)

**Download from:** https://getcomposer.org/download/

Or for Windows with XAMPP:

- Download `Composer-Setup.exe`
- Run installer
- Restart your terminal/command prompt

### 2. Install PHP Dependencies

Open PowerShell in your project folder and run:

```powershell
composer install
```

This will install the Google BigQuery PHP library.

### 3. Configure Your MySQL Database

Edit `config/database.php` and update:

```php
define('DB_NAME', 'your_actual_database_name');  // Your booking database
define('DB_USER', 'root');  // Your MySQL username
define('DB_PASS', '');      // Your MySQL password
```

### 4. Set Up Google Cloud (Continue from screenshot)

You already enabled BigQuery API. Now:

#### A. Create Service Account

1. In Google Cloud Console, click **"Credentials"** (left sidebar)
2. Click **"Create Credentials"** → **"Service Account"**
3. Name: `bigquery-analytics`
4. Click **"Create and Continue"**
5. Grant role: **"BigQuery Admin"**
6. Click **"Done"**

#### B. Download JSON Key

1. Click on the service account you just created
2. Go to **"Keys"** tab
3. Click **"Add Key"** → **"Create new key"**
4. Choose **JSON** format
5. Download the file

#### C. Save Credentials

1. **Important:** The downloaded file might be named `google-credentials.json.json` (double extension)
   - Windows sometimes hides file extensions, so it looks like `.json` but is actually `.json.json`
   - You need to rename it to just `google-credentials.json` (single extension)
2. **How to fix:**

   - Right-click the file → Properties → Check the full name
   - Or in File Explorer: View menu → Check "File name extensions"
   - Rename to: `google-credentials.json` (no double .json.json)

3. Move it to: `D:\xampp\htdocs\Analyticsz\storage\google-credentials.json`

4. **Verify the location:**
   - Final path should be: `D:\xampp\htdocs\Analyticsz\storage\google-credentials.json`
   - The file should contain JSON data starting with `{"type": "service_account"...}`

### 5. Create BigQuery Dataset

In Google Cloud Console:

1. Search for "BigQuery" in the top search bar
2. Click on your project name in the left panel
3. Click **"Create Dataset"**
4. Dataset ID: `booking_analytics`
5. Data location: Choose closest to you (e.g., `US`, `EU`)
6. Click **"Create Dataset"**

### 6. Run Setup Script

Once steps 1-5 are complete, open your browser:

```
http://localhost/Analyticsz/analytics/setup/setup_analytics_tables.php
```

This will:

- ✅ Test BigQuery connection
- ✅ Create necessary tables
- ✅ Insert sample data for testing

### 7. Migrate Your Data

After setup is successful:

```
http://localhost/Analyticsz/analytics/setup/run_migration.php
```

This will copy your existing bookings from MySQL to BigQuery.

### 8. View Analytics Dashboard

```
http://localhost/Analyticsz/analytics/
```

## 🆘 Troubleshooting

### "composer: command not found"

- Install Composer from https://getcomposer.org/download/
- Restart your terminal after installation

### "BigQuery credentials file not found"

- Make sure the file is at: `storage/google-credentials.json`
- Check the file name is exactly: `google-credentials.json` (no extra extensions)
- **Common issue:** Downloaded as `google-credentials.json.json` (double extension)
  - Enable "File name extensions" in Windows File Explorer (View menu)
  - Rename to remove the extra `.json`
- Verify with: Open the file in Notepad - it should show JSON starting with `{"type": "service_account"`

### "Database connection failed"

- Update `config/database.php` with your actual database name
- Make sure XAMPP MySQL is running

### "Class 'Google\Cloud\BigQuery\BigQueryClient' not found"

- Run `composer install` to install dependencies
- Make sure `vendor/` folder exists after installation

## 📊 What This System Does

Once set up, you'll get:

- 📈 **Booking Forecasts** - Predict next 30 days of bookings
- 💰 **Revenue Predictions** - Forecast revenue with confidence intervals
- 🤖 **ML Models** - Powered by Google BigQuery ML (ARIMA_PLUS)
- 📉 **Historical Trends** - Analyze past performance
- 🎯 **Model Accuracy** - Performance metrics (MAE, RMSE, R²)

## 💰 Cost Warning

BigQuery ML is a **paid service**. Costs include:

- Storage: ~$0.02 per GB per month
- Queries: First 1 TB free per month, then $5 per TB
- ML Model Training: ~$0.25-$1 per model training

For small projects (< 1000 bookings), monthly cost is typically **$1-5**.

## 📚 Additional Resources

- `analytics/INTEGRATION_GUIDE.md` - Detailed integration guide
- `analytics/docs/ML_PREDICTION_GUIDE.md` - ML model documentation
- `analytics/docs/bigquery_integration_guide.md` - BigQuery setup details

---

**Ready?** Start with Step 1 (Install Composer) and work your way through!
