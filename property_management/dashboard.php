<?php
// dashboard.php - Redirect to appropriate dashboard
session_start();

if (!isset($_SESSION['role'])) {
    // Not logged in, redirect to login
    header("Location: index.php");
    exit();
}

if ($_SESSION['role'] === 'owner') {
    // Owner dashboard
    header("Location: owner_dashboard.php");
} else {
    // Tenant dashboard
    header("Location: tenant_dashboard.php");
}
exit();
?>