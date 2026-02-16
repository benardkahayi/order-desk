<?php
require 'db.php';
$id = $_GET['id'];
$action = $_GET['action'];
if($action=='approve') $conn->prepare("UPDATE payments SET status='Approved' WHERE id=?")->execute([$id]);
if($action=='reject') $conn->prepare("UPDATE payments SET status='Rejected' WHERE id=?")->execute([$id]);
header("Location: admin_dashboard.php?section=payments");
?>
