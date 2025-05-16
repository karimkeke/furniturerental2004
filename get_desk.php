<?php
include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'desk' limit 1");
$stmt->execute();
$desk = $stmt->get_result();
?>
