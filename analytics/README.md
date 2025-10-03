# 📊 KingLang Analytics System

This folder contains all analytics-related components for the KingLang Transport booking system.

## 📁 Folder Structure

```
analytics/
├── index.php                 # Main analytics hub (entry point)
├── analytics.php             # Analytics configuration
├── dashboard/                # Analytics dashboards and reports
│   ├── simple_analytics.php
│   ├── detailed_analytics.php
│   ├── analytics_dashboard.php
│   └── analytics_direct.php
├── setup/                    # Setup and installation scripts
│   ├── setup_analytics.php
│   ├── setup_analytics_tables.php
│   ├── setup_ml_with_billing.php
│   ├── install_analytics.php
│   ├── run_migration.php
│   └── migrate_to_bigquery.php
├── data/                     # Data management tools
│   ├── add_sample_data.php
│   ├── insert_sample_data.php
│   ├── insert_data_sql.php
│   ├── create_sample_data_csv.php
│   └── load_csv_to_bigquery.php
└── docs/                     # Documentation and guides
    ├── ANALYTICS_README.md
    ├── ANALYTICS_SETUP_COMPLETE.md
    ├── bigquery_analytics.sql
    └── bigquery_integration_guide.md
```

## 🚀 Quick Start

1. **Access Analytics Hub**: Visit `http://localhost:8000/analytics/`
2. **Setup Analytics**: Run setup scripts in the `setup/` folder
3. **View Dashboards**: Access dashboards in the `dashboard/` folder
4. **Manage Data**: Use tools in the `data/` folder

## 📊 Available Dashboards

- **Simple Analytics**: Basic analytics overview
- **Detailed Analytics**: Comprehensive analytics with ML predictions
- **Full Dashboard**: Complete analytics interface
- **Direct Analytics**: Direct BigQuery access

## 🔧 Setup Process

1. Run `setup/setup_analytics.php` to initialize the system
2. Run `setup/setup_analytics_tables.php` to create BigQuery tables
3. Run `setup/setup_ml_with_billing.php` to set up ML models
4. Run `data/add_sample_data.php` to add sample data
5. Access dashboards to view analytics

## 📚 Documentation

- **ANALYTICS_README.md**: Complete analytics system documentation
- **ANALYTICS_SETUP_COMPLETE.md**: Setup completion guide
- **bigquery_analytics.sql**: SQL queries for BigQuery
- **bigquery_integration_guide.md**: BigQuery integration guide

## 🔗 Access URLs

- **Analytics Hub**: `http://localhost:8000/analytics/`
- **Simple Analytics**: `http://localhost:8000/analytics/dashboard/simple_analytics.php`
- **Detailed Analytics**: `http://localhost:8000/analytics/dashboard/detailed_analytics.php`
- **Setup Analytics**: `http://localhost:8000/analytics/setup/setup_analytics.php`

## ⚠️ Important Notes

- Make sure BigQuery credentials are properly configured
- Database connection must be working
- ML models require BigQuery ML setup
- Sample data should be loaded for meaningful analytics

## 🔙 Back to Main System

To return to the main KingLang Transport system, click the "Back to KingLang Transport" button or visit `http://localhost:8000/`
