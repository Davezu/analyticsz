<?php
declare(strict_types=1); 
date_default_timezone_set('Asia/Manila');

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>KingLang Analytics Hub</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }";
echo ".container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".btn { display: inline-block; padding: 12px 24px; margin: 10px 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #1e7e34; }";
echo ".btn-warning { background: #ffc107; color: #212529; }";
echo ".btn-warning:hover { background: #e0a800; }";
echo ".btn-info { background: #17a2b8; }";
echo ".btn-info:hover { background: #138496; }";
echo ".section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }";
echo ".success { color: #28a745; }";
echo ".error { color: #dc3545; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>📊 KingLang Analytics Hub</h1>";
echo "<p class='success'>✅ Analytics system organized and ready!</p>";

echo "<div class='section'>";
echo "<h2>🎯 Dashboard & Reports</h2>";
echo "<p>Access your analytics dashboards and reports:</p>";
echo "<a href='dashboard/simple_analytics.php' class='btn btn-success'>📊 Simple Analytics</a>";
echo "<a href='dashboard/detailed_analytics.php' class='btn btn-success'>📈 Detailed Analytics</a>";
echo "<a href='dashboard/analytics_dashboard.php' class='btn btn-success'>🎛️ Full Dashboard</a>";
echo "<a href='dashboard/analytics_direct.php' class='btn btn-success'>⚡ Direct Analytics</a>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🔧 Setup & Installation</h2>";
echo "<p>Setup and configuration tools:</p>";
echo "<a href='setup/setup_analytics.php' class='btn btn-warning'>⚙️ Setup Analytics</a>";
echo "<a href='setup/setup_analytics_tables.php' class='btn btn-warning'>📋 Setup Tables</a>";
echo "<a href='setup/setup_ml_with_billing.php' class='btn btn-warning'>🤖 Setup ML Models</a>";
echo "<a href='setup/install_analytics.php' class='btn btn-warning'>📦 Install Analytics</a>";
echo "<a href='setup/run_migration.php' class='btn btn-warning'>🔄 Run Migration</a>";
echo "<a href='setup/migrate_to_bigquery.php' class='btn btn-warning'>☁️ Migrate to BigQuery</a>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📊 Data Management</h2>";
echo "<p>Data insertion and management tools:</p>";
echo "<a href='data/add_sample_data.php' class='btn btn-info'>➕ Add Sample Data</a>";
echo "<a href='data/insert_sample_data.php' class='btn btn-info'>📥 Insert Sample Data</a>";
echo "<a href='data/insert_data_sql.php' class='btn btn-info'>🗄️ Insert SQL Data</a>";
echo "<a href='data/create_sample_data_csv.php' class='btn btn-info'>📄 Create CSV Data</a>";
echo "<a href='data/load_csv_to_bigquery.php' class='btn btn-info'>📤 Load CSV to BigQuery</a>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📚 Documentation</h2>";
echo "<p>Analytics documentation and guides:</p>";
echo "<a href='docs/ANALYTICS_README.md' class='btn'>📖 Analytics README</a>";
echo "<a href='docs/ANALYTICS_SETUP_COMPLETE.md' class='btn'>✅ Setup Complete Guide</a>";
echo "<a href='docs/bigquery_analytics.sql' class='btn'>🗃️ BigQuery SQL</a>";
echo "<a href='docs/bigquery_integration_guide.md' class='btn'>🔗 Integration Guide</a>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>🔙 Back to Main System</h2>";
echo "<a href='../index.php' class='btn'>🏠 Back to KingLang Transport</a>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 