<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = trim($_POST['login_input'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($login_input && $password) {
        // Find customer by phone
        $stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->execute([$login_input]);
        $customer = $stmt->fetch();

        // If not found, find by recent order ID
        if (!$customer) {
            $order_stmt = $conn->prepare("
                SELECT c.*
                FROM customers c
                JOIN orders o ON c.id = o.customer_id
                WHERE o.order_id = ?
                ORDER BY o.date_created DESC
                LIMIT 1
            ");
            $order_stmt->execute([$login_input]);
            $customer = $order_stmt->fetch();
        }

        if ($customer) {
            $valid_password = false;

            // Get recent order
            $order_stmt = $conn->prepare("
                SELECT order_id
                FROM orders
                WHERE customer_id = ?
                ORDER BY date_created DESC
                LIMIT 1
            ");
            $order_stmt->execute([$customer['id']]);
            $recent_order = $order_stmt->fetch();

            // First time login: password = recent order ID
            if ($recent_order && $password === $recent_order['order_id']) {
                $valid_password = true;
                $hashed_password = password_hash($recent_order['order_id'], PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
                $update_stmt->execute([$hashed_password, $customer['id']]);
            }
            // Subsequent login: check hashed password
            elseif (!empty($customer['password']) && password_verify($password, $customer['password'])) {
                $valid_password = true;
            }

            if ($valid_password) {
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                $_SESSION['customer_phone'] = $customer['phone'];
                $_SESSION['role'] = 'customer';
                $_SESSION['order_id'] = $recent_order['order_id'] ?? '';

                header("Location: customer_dashboard.php");
                exit;
            } else {
                $error = "Password si sahihi. Jaribu kutumia order ID yako ya hivi karibuni.";
            }
        } else {
            $error = "Hakuna mteja au order ID iliyopatikana. Hakikisha umeingiza taarifa sahihi.";
        }
    } else {
        $error = "Tafadhali jaza namba ya simu/order ID na password.";
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingia kwa Mteja - Order Desk</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0, 128, 96, 0.1);
    padding: 40px;
    width: 100%;
    max-width: 440px;
}
.login-title { font-size: 1.8rem; font-weight: 700; color: #202123; margin-bottom: 8px; }
.login-subtitle { color: #6b7177; font-size: 1rem; margin-bottom: 20px; }
.form-group { margin-bottom: 24px; }
.form-control { width: 100%; padding: 14px; border-radius: 8px; border: 2px solid #e1e5e9; }
.btn-login { width: 100%; padding: 16px; background: #008060; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-login:hover { background: #006e52; }
.alert { margin-bottom: 24px; padding: 16px; border-radius: 8px; display: flex; align-items: center; gap: 12px; }
.alert-danger { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
.info-box { background: #f2fcf5; border-left: 4px solid #008060; padding: 16px; border-radius: 8px; margin-bottom: 24px; }
</style>
</head>
<body>
<div class="login-card">
    <h1 class="login-title">Ingia kwa Mteja</h1>
    <p class="login-subtitle">Tumia namba ya simu au order ID</p>

    <?php if($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <div class="info-box">
        <p>• Ingiza namba ya simu uliyojisajili au order ID</p>
        <p>• Kwa mara ya kwanza, tumia order ID kama password</p>
        <p>• Oda ya hivi karibuni itachaguliwa baada ya kuingia</p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label>Namba ya Simu / Order ID</label>
            <input type="text" class="form-control" name="login_input" placeholder="0712345678 au ORD12345" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="password" placeholder="Ingiza password yako" required>
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt me-2"></i> Ingia</button>
    </form>
</div>
</body>
</html>
