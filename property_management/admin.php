<?php
// admin.php - Quick database management
session_start();

// Simple password protection
if (!isset($_POST['admin_pass']) || $_POST['admin_pass'] !== 'admin123') {
    ?>
    <form method="POST">
        <h2>Admin Access</h2>
        <input type="password" name="admin_pass" placeholder="Enter admin password">
        <button type="submit">Access</button>
    </form>
    <?php
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'property_management');
?>

<h1>🔧 Database Admin Panel</h1>

<?php
// Handle actions
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'reset') {
        // Reset sample data
        include 'initializeDatabase.php';
        echo "<p>✅ Database reset completed</p>";
    }
}
?>

<h3>Database Operations:</h3>
<a href="?action=reset" onclick="return confirm('Reset all data?')">🔄 Reset Sample Data</a>

<h3>Current Data Summary:</h3>
<?php
$tables = $conn->query("SHOW TABLES");
while ($table = $tables->fetch_array()) {
    $table_name = $table[0];
    $count = $conn->query("SELECT COUNT(*) as c FROM $table_name")->fetch_assoc()['c'];
    echo "<p>$table_name: $count records</p>";
}
?>