<?php
require 'db.php';
session_start();

if ($_POST) {
    $stmt = $conn->prepare("
        INSERT INTO feedback (customer_id, message)
        VALUES (?, ?)
    ");
    $stmt->execute([$_SESSION['customer_id'], $_POST['message']]);
    echo "Feedback sent";
}
?>

<form method="POST">
    <textarea name="message" class="form-control mb-3" placeholder="Your feedback"></textarea>
    <button class="btn btn-primary">Send</button>
</form>
