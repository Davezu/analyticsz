<?php
/**
 * Detailed Analytics Dashboard
 * 
 * Shows actual data tables, charts, and values
 */



echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Detailed Analytics - KingLang</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>";
echo "<style>";
echo "body { background-color: #f8f9fa; }";
echo ".analytics-card { background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 30px; }";
echo ".data-table { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".data-table th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }";
echo ".metric-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 20px; text-align: center; }";
echo ".metric-value { font-size: 2.5rem; font-weight: bold; margin-bottom: 5px; }";
echo ".metric-label { font-size: 0.9rem; opacity: 0.9; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container-fluid py-4'>";
echo "<div class='row'>";
echo "<div class='col-12'>";
echo "<h1 class='mb-4'>📊 Detailed Analytics Dashboard</h1>";
echo "<p class='text-muted'>Real-time booking analytics and insights</p>";
echo "</div>";
echo "</div>";

try {
    // Include required files
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../app/Classes/BigQueryHelper.php';
    
    $bigQueryHelper = new App\Classes\BigQueryHelper();
    
    // Get all analytics data
    $bookingForecast = $bigQueryHelper->getBookingForecast();
    $revenueForecast = $bigQueryHelper->getRevenueForecast();
    $modelPerformance = $bigQueryHelper->getModelPerformance();
    $dailyTrends = $bigQueryHelper->getDailyTrends();
    
    // Calculate summary metrics
    $totalBookings = array_sum(array_column($dailyTrends, 'total_bookings'));
    $totalRevenue = array_sum(array_column($dailyTrends, 'total_revenue'));
    $avgBookingsPerDay = $totalBookings / count($dailyTrends);
    
    // Summary Metrics Row
    echo "<div class='row mb-4'>";
    echo "<div class='col-md-4'>";
    echo "<div class='metric-card'>";
    echo "<div class='metric-value'>" . number_format($totalBookings) . "</div>";
    echo "<div class='metric-label'>Total Bookings</div>";
    echo "</div>";
    echo "</div>";
    echo "<div class='col-md-4'>";
    echo "<div class='metric-card'>";
    echo "<div class='metric-value'>$" . number_format($totalRevenue) . "</div>";
    echo "<div class='metric-label'>Total Revenue</div>";
    echo "</div>";
    echo "</div>";
    echo "<div class='col-md-4'>";
    echo "<div class='metric-card'>";
    echo "<div class='metric-value'>" . number_format($avgBookingsPerDay, 1) . "</div>";
    echo "<div class='metric-label'>Avg Bookings/Day</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // Booking Forecast Section
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<div class='analytics-card'>";
    echo "<h3>📈 Booking Forecast (Next 7 Days)</h3>";
    echo "<div class='data-table'>";
    echo "<table class='table table-striped mb-0'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Predicted Bookings</th>";
    echo "<th>Range</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($bookingForecast as $forecast) {
        echo "<tr>";
        echo "<td>" . $forecast['forecast_date'] . "</td>";
        echo "<td><strong>" . $forecast['predicted_bookings'] . "</strong></td>";
        echo "<td>" . $forecast['lower_bound'] . " - " . $forecast['upper_bound'] . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // Revenue Forecast Section
    echo "<div class='col-md-6'>";
    echo "<div class='analytics-card'>";
    echo "<h3>💰 Revenue Forecast (Next 7 Days)</h3>";
    echo "<div class='data-table'>";
    echo "<table class='table table-striped mb-0'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Predicted Revenue</th>";
    echo "<th>Range</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($revenueForecast as $forecast) {
        echo "<tr>";
        echo "<td>" . $forecast['forecast_date'] . "</td>";
        echo "<td><strong>$" . number_format($forecast['predicted_revenue']) . "</strong></td>";
        echo "<td>$" . number_format($forecast['lower_bound']) . " - $" . number_format($forecast['upper_bound']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // Daily Trends Section
    echo "<div class='row'>";
    echo "<div class='col-12'>";
    echo "<div class='analytics-card'>";
    echo "<h3>📊 Daily Booking Trends (Last 10 Days)</h3>";
    echo "<div class='data-table'>";
    echo "<table class='table table-striped mb-0'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Total Bookings</th>";
    echo "<th>Total Revenue</th>";
    echo "<th>Confirmed</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($dailyTrends as $trend) {
        echo "<tr>";
        echo "<td>" . $trend['date'] . "</td>";
        echo "<td><strong>" . $trend['total_bookings'] . "</strong></td>";
        echo "<td>$" . number_format($trend['total_revenue']) . "</td>";
        echo "<td>" . $trend['confirmed_bookings'] . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // Model Performance Section
    echo "<div class='row'>";
    echo "<div class='col-12'>";
    echo "<div class='analytics-card'>";
    echo "<h3>🤖 ML Model Performance</h3>";
    echo "<div class='data-table'>";
    echo "<table class='table table-striped mb-0'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Model Name</th>";
    echo "<th>Model Type</th>";
    echo "<th>Status</th>";
    echo "<th>Accuracy</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($modelPerformance as $model) {
        $statusBadge = $model['status'] === 'Trained' ? 
            '<span class="badge bg-success">Trained</span>' : 
            '<span class="badge bg-warning">Not Trained</span>';
        echo "<tr>";
        echo "<td>" . ucfirst(str_replace('_', ' ', $model['model_name'])) . "</td>";
        echo "<td>" . $model['model_type'] . "</td>";
        echo "<td>" . $statusBadge . "</td>";
        echo "<td>" . ($model['accuracy'] > 0 ? round($model['accuracy'] * 100, 1) . '%' : 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // Charts Section
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<div class='analytics-card'>";
    echo "<h3>📈 Booking Trends Chart</h3>";
    echo "<canvas id='bookingChart' width='400' height='200'></canvas>";
    echo "</div>";
    echo "</div>";
    echo "<div class='col-md-6'>";
    echo "<div class='analytics-card'>";
    echo "<h3>💰 Revenue Trends Chart</h3>";
    echo "<canvas id='revenueChart' width='400' height='200'></canvas>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // Navigation
    echo "<div class='row mt-4'>";
    echo "<div class='col-12 text-center'>";
    echo "<a href='simple_analytics.php' class='btn btn-primary me-2'>← Back to Overview</a>";
    echo "<a href='index.php' class='btn btn-secondary'>Back to Home</a>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>"; // Close container

// JavaScript for charts
echo "<script>";
echo "// Booking Trends Chart";
echo "const bookingCtx = document.getElementById('bookingChart').getContext('2d');";
echo "new Chart(bookingCtx, {";
echo "  type: 'line',";
echo "  data: {";
echo "    labels: " . json_encode(array_column(array_reverse($dailyTrends), 'date')) . ",";
echo "    datasets: [{";
echo "      label: 'Total Bookings',";
echo "      data: " . json_encode(array_column(array_reverse($dailyTrends), 'total_bookings')) . ",";
echo "      borderColor: '#667eea',";
echo "      backgroundColor: 'rgba(102, 126, 234, 0.1)',";
echo "      tension: 0.4";
echo "    }]";
echo "  },";
echo "  options: {";
echo "    responsive: true,";
echo "    plugins: {";
echo "      legend: { display: false }";
echo "    }";
echo "  }";
echo "});";

echo "// Revenue Trends Chart";
echo "const revenueCtx = document.getElementById('revenueChart').getContext('2d');";
echo "new Chart(revenueCtx, {";
echo "  type: 'bar',";
echo "  data: {";
echo "    labels: " . json_encode(array_column(array_reverse($dailyTrends), 'date')) . ",";
echo "    datasets: [{";
echo "      label: 'Total Revenue',";
echo "      data: " . json_encode(array_column(array_reverse($dailyTrends), 'total_revenue')) . ",";
echo "      backgroundColor: '#764ba2',";
echo "      borderColor: '#667eea',";
echo "      borderWidth: 1";
echo "    }]";
echo "  },";
echo "  options: {";
echo "    responsive: true,";
echo "    plugins: {";
echo "      legend: { display: false }";
echo "    }";
echo "  }";
echo "});";
echo "</script>";

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body>";
echo "</html>"; 