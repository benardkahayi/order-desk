<?php
require 'db.php';
session_start();

if ($_POST) {
    $stmt = $conn->prepare("
        INSERT INTO refunds (order_id, reason)
        VALUES (?, ?)
    ");
    $stmt->execute([$_POST['order_id'], $_POST['reason']]);
    echo "Refund requested";
}
?>

<form method="POST">
    <input name="order_id" class="form-control mb-2" placeholder="Order ID">
    <textarea name="reason" class="form-control mb-3" placeholder="Reason"></textarea>
    <button class="btn btn-danger">Submit</button>
</form>
