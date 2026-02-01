<?php
// test.php
echo "<h1>🔧 System Test Page</h1>";

// Test database connection
$conn = new mysqli('localhost', 'root', '', 'property_management');

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database Connection Failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Database Connection Successful</p>";
    
    // Check tables
    $tables = ['owners', 'tenants', 'buildings', 'apartments', 'leases', 'payments', 'maintenance_requests'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p>✅ Table '$table' exists</p>";
            
            // Count records
            $count = $conn->query("SELECT COUNT(*) as c FROM $table")->fetch_assoc()['c'];
            echo "<p style='margin-left: 20px;'>Records: $count</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Table '$table' not found</p>";
        }
    }
}

$conn->close();

echo "<hr>";
echo "<h3>Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='index.php'>Login Page</a></li>";
echo "<li><a href='admin.php'>Admin Panel</a></li>";
echo "</ul>";
?>