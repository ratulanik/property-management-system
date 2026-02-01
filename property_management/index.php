<?php

// index.php - Apartment Management System Login Page
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'owner') {
        header("Location: owner_dashboard.php");
    } else {
        header("Location: tenant_dashboard.php");
    }
    exit();
}

// Database configuration
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'apartment_rental_system';

// Create connection
 $conn = new mysqli($host, $user, $password, $database);


// If database doesn't exist, create it
if ($conn->connect_error) {
    // Try to create database
    $conn = new mysqli($host, $user, $password);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $database 
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        $conn->select_db($database);
        // Initialize database
        initializeDatabase($conn);
    }
}

// Function to initialize database
function initializeDatabase($conn) {
    $sql = "
    CREATE TABLE IF NOT EXISTS owners (
        owner_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255) DEFAULT '123456',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS tenants (
        tenant_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255) DEFAULT '123456',
        move_in_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS buildings (
        building_id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT,
        building_name VARCHAR(100),
        address VARCHAR(255) NOT NULL,
        total_floors INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES owners(owner_id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS apartments (
        apartment_id INT AUTO_INCREMENT PRIMARY KEY,
        building_id INT NOT NULL,
        unit_number VARCHAR(20) NOT NULL,
        floor INT,
        bedrooms INT,
        bathrooms INT,
        rent DECIMAL(10,2) NOT NULL,
        status ENUM('available', 'rented') DEFAULT 'available',
        apartment_number VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (building_id) REFERENCES buildings(building_id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS leases (
        lease_id INT AUTO_INCREMENT PRIMARY KEY,
        apartment_id INT NOT NULL,
        tenant_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE,
        monthly_rent DECIMAL(10,2) NOT NULL,
        status ENUM('active', 'ended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (apartment_id) REFERENCES apartments(apartment_id) ON DELETE CASCADE,
        FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        lease_id INT NOT NULL,
        payment_date DATE NOT NULL,
        payment_month VARCHAR(20),
        amount DECIMAL(10,2) NOT NULL,
        method ENUM('cash', 'bank', 'card', 'mobile'),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (lease_id) REFERENCES leases(lease_id) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS maintenance_requests (
        request_id INT AUTO_INCREMENT PRIMARY KEY,
        apartment_id INT NOT NULL,
        tenant_id INT,
        issue_description TEXT NOT NULL,
        request_date DATE DEFAULT (CURRENT_DATE),
        status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (apartment_id) REFERENCES apartments(apartment_id) ON DELETE CASCADE,
        FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE SET NULL
    );
    ";
    
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    }
    
    // Insert sample data if tables are empty
    checkAndInsertSampleData($conn);
}

// Function to insert sample data
function checkAndInsertSampleData($conn) {
    // Check if owners table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM owners");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $sampleData = "
        INSERT INTO owners (full_name, phone, email) VALUES
        ('Rahim Ahmed', '01711111111', 'rahim@gmail.com'),
        ('Karim Hossain', '01822222222', 'karim@gmail.com'),
        ('Fatima Begum', '01933333333', 'fatima@gmail.com');
        
        INSERT INTO tenants (full_name, phone, email) VALUES
        ('Sajid Khan', '01933333333', 'sajid@gmail.com'),
        ('Nusrat Jahan', '01644444444', 'nusrat@gmail.com'),
        ('Amina Rahman', '01755555555', 'amina@gmail.com');
        
        INSERT INTO buildings (owner_id, building_name, address, total_floors) VALUES
        (1, 'Green View Apartments', 'Dhanmondi, Dhaka', 10),
        (2, 'Skyline Residency', 'Uttara, Dhaka', 12),
        (3, 'Lake View Tower', 'Gulshan, Dhaka', 8);
        
        INSERT INTO apartments (building_id, unit_number, floor, bedrooms, bathrooms, rent, status) VALUES
        (1, 'A1', 1, 2, 1, 25000.00, 'rented'),
        (1, 'A2', 2, 3, 2, 35000.00, 'available'),
        (2, 'B1', 3, 2, 2, 30000.00, 'rented'),
        (2, 'B2', 5, 3, 2, 40000.00, 'available'),
        (3, 'C1', 1, 1, 1, 20000.00, 'rented'),
        (3, 'C2', 2, 2, 1, 28000.00, 'available');
        
        INSERT INTO leases (apartment_id, tenant_id, start_date, end_date, monthly_rent, status) VALUES
        (1, 1, '2024-01-01', '2024-12-31', 25000.00, 'active'),
        (3, 2, '2024-03-15', '2025-03-14', 30000.00, 'active'),
        (5, 3, '2024-02-01', '2025-01-31', 20000.00, 'active');
        ";
        
        $conn->multi_query($sampleData);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏢 Apartment Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #7f8c8d;
            font-size: 15px;
        }
        
        .logo-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 25px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            background: #f8f9fa;
        }
        
        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .tab:hover {
            background: #e9ecef;
        }
        
        .tab.active {
            background: #667eea;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
            background: #fafafa;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .password-info {
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0,0,0,0.1);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .link p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            border: none;
        }
        
        .success {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #667eea;
        }
        
        .demo-credentials h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 13px;
        }
        
        .credential-role {
            color: #667eea;
            font-weight: 600;
        }
        
        .credential-email {
            color: #2c3e50;
        }
        
        .credential-password {
            color: #27ae60;
            font-weight: 600;
        }
        
        .system-info {
            text-align: center;
            margin-top: 30px;
            color: #95a5a6;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <span class="logo-icon">🏢</span>
            <h1>Apartment Management</h1>
            <p>Login to access your dashboard</p>
        </div>
        
        <?php
        // Display messages
        if (isset($_GET['error'])) {
            echo '<div class="message error">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        if (isset($_GET['success'])) {
            echo '<div class="message success">' . htmlspecialchars($_GET['success']) . '</div>';
        }
        ?>
        
        <!-- Demo Credentials -->
        <div class="demo-credentials">
            <h4>🔐 Demo Login Credentials:</h4>
            <div class="credential-item">
                <span class="credential-role">Owner</span>
                <span class="credential-email">rahim@gmail.com</span>
                <span class="credential-password">123456</span>
            </div>
            <div class="credential-item">
                <span class="credential-role">Tenant</span>
                <span class="credential-email">sajid@gmail.com</span>
                <span class="credential-password">123456</span>
            </div>
        </div>
        
        <!-- Role Selection Tabs -->
        <div class="tabs">
            <button type="button" class="tab active" onclick="setRole('owner')">Owner Login</button>
            <button type="button" class="tab" onclick="setRole('tenant')">Tenant Login</button>
        </div>
        
        <!-- Login Form - FIXED AUTO-FILL ISSUE -->
        <form method="POST" action="login.php" id="loginForm" autocomplete="off">
            <input type="hidden" id="role" name="role" value="owner">
            
            <div class="form-group">
                <label for="email">📧 Email Address</label>
                <input type="email" id="email" name="email" required 
                       placeholder="Type: rahim@gmail.com"
                       autocomplete="off"
                       value="rahim@gmail.com">
            </div>
            
            <div class="form-group">
                <label for="password">🔒 Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Type: 123456"
                       autocomplete="new-password"
                       value="123456">
                <span class="password-info">Default password: 123456</span>
            </div>
            
            <!-- Trick browser auto-fill -->
            <input type="text" style="display:none;">
            <input type="password" style="display:none;">
            
            <button type="submit" class="btn">
                <span id="btnText">Login as Owner</span>
            </button>
        </form>
        
        <div class="link">
            <p>Don't have an account? <a href="register.php">Create new account</a></p>
            <p><a href="test.php">System Test</a> | <a href="admin.php">Admin Panel</a></p>
        </div>
        
        <div class="system-info">
            <p>Property Management System v1.0 | Database: property_management</p>
        </div>
    </div>
    
    <script>
        function setRole(role) {
            // Update hidden input
            document.getElementById('role').value = role;
            
            // Update tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update button text
            document.getElementById('btnText').textContent = 'Login as ' + 
                (role === 'owner' ? 'Owner' : 'Tenant');
            
            // Auto-fill demo credentials
            if (role === 'owner') {
                document.getElementById('email').value = 'rahim@gmail.com';
                document.getElementById('password').value = '123456';
            } else {
                document.getElementById('email').value = 'sajid@gmail.com';
                document.getElementById('password').value = '123456';
            }
        }
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
            
            // Show loading state
            const btn = e.target.querySelector('.btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Logging in...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        });
        
        // Clear any browser auto-fill on page load
        window.onload = function() {
            // Force set values
            document.getElementById('email').value = 'rahim@gmail.com';
            document.getElementById('password').value = '123456';
            
            // Clear browser auto-fill
            setTimeout(function() {
                document.getElementById('email').value = 'rahim@gmail.com';
                document.getElementById('password').value = '123456';
            }, 100);
        };
    </script>
    
    <?php
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>