<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Get pending orders for dropdown
$orders = $conn->query("
    SELECT order_id, product_name, total_price, customer_name 
    FROM orders 
    WHERE status = 'Processing' OR status = 'Shipped'
    ORDER BY date_created DESC
")->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = trim($_POST['order_id'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? '');
    $status = trim($_POST['status'] ?? 'Pending');
    
    if (empty($order_id) || $amount <= 0 || empty($payment_method)) {
        $error = "Tafadhali jaza sehemu zote zinazohitajika.";
    } else {
        try {
            // Get order details
            $stmt = $conn->prepare("SELECT customer_id, total_price FROM orders WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();
            
            if (!$order) {
                throw new Exception("Oda haijapatikana.");
            }
            
            // Insert payment
            $stmt = $conn->prepare("
                INSERT INTO payments (order_id, customer_id, amount, payment_method, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $order_id,
                $order['customer_id'],
                $amount,
                $payment_method,
                $status
            ]);
            
            // Update order payment status if approved
            if ($status == 'Approved') {
                $conn->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?")
                     ->execute([$order_id]);
            }
            
            $success = "Malipo yamerekodiwa kikamilifu!";
            $_POST = [];
            
        } catch (PDOException $e) {
            $error = "Hitilafu: " . $e->getMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Rekodi Malipo - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Rekodi Malipo</h2>
        <a href="payments.php" class="btn btn-secondary mb-3">← Rudi</a>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Chagua Oda</label>
                        <select name="order_id" class="form-select" required>
                            <option value="">-- Chagua Oda --</option>
                            <?php foreach ($orders as $order): ?>
                                <option value="<?= $order['order_id'] ?>">
                                    <?= $order['order_id'] ?> - <?= $order['product_name'] ?> 
                                    (TZS <?= number_format($order['total_price'], 0) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Kiasi (TZS)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Njia ya Malipo</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">-- Chagua Njia --</option>
                                <option value="M-Pesa">M-Pesa</option>
                                <option value="Airtel Money">Airtel Money</option>
                                <option value="Tigo Pesa">Tigo Pesa</option>
                                <option value="Bank Transfer">Uhamisho wa Benki</option>
                                <option value="Credit Card">Kadi ya Mkopo</option>
                                <option value="Cash">Fedha Taslimu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Hali ya Malipo</label>
                        <select name="status" class="form-select">
                            <option value="Pending">Inasubiri</option>
                            <option value="Approved">Imekubaliwa</option>
                            <option value="Failed">Imekataliwa</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Rekodi Malipo</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>