<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'apartment_rental_system');

$email = $conn->real_escape_string($_POST['email']);
$password_input = $conn->real_escape_string($_POST['password']);
$role = $_POST['role'];

if ($role === 'owner') {
    $sql = "SELECT * FROM owners WHERE email = '$email'";
    $redirect = 'owner_dashboard.php';
    $id_field = 'owner_id';
} else {
    $sql = "SELECT * FROM tenants WHERE email = '$email'";
    $redirect = 'tenant_dashboard.php';
    $id_field = 'tenant_id';
}

$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    
    if(trim($password_input) === trim($user['password'])) {
        $_SESSION['user_id'] = $user[$id_field];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $role;
        
        header("Location: $redirect");
        exit();
    } else {
        header("Location: index.php?error=Wrong password!");
        exit();
    }
} else {
    header("Location: index.php?error=User not found!");
    exit();
}
$conn->close();
?>