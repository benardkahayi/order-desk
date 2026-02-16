<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit;
}

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $customer_id = $_SESSION['customer_id'];
    
    // Verify order belongs to customer
    $stmt = $conn->prepare("
        SELECT id FROM orders 
        WHERE order_id = ? AND customer_id = ?
    ");
    $stmt->execute([$order_id, $customer_id]);
    
    if ($stmt->fetch()) {
        $_SESSION['order_id'] = $order_id;
        header("Location: customer_dashboard.php");
        exit;
    }
}

header("Location: customer_dashboard.php");
exit;
?>