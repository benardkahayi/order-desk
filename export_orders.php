<?php
session_start();
require '../db.php';

// Only admin can export orders
if (!isset($_SESSION['admin_logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Fetch all orders
$stmt = $conn->query("
    SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    ORDER BY o.date_created DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_' . date('Ymd_His') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Column headers
fputcsv($output, [
    'ID', 'Order ID', 'Customer Name', 'Customer Phone', 
    'Product', 'Quantity', 'Unit Price', 'Total Price', 'Status', 'Date Created'
]);

// Output each order
foreach ($orders as $ord) {
    fputcsv($output, [
        $ord['id'],
        $ord['order_id'],
        $ord['customer_name'],
        $ord['customer_phone'],
        $ord['product_name'],
        $ord['quantity'],
        $ord['unit_price'],
        $ord['total_price'],
        $ord['status'],
        $ord['date_created'],
    ]);
}

// Close output stream
fclose($output);
exit();
