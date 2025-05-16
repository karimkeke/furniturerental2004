<?php
include('connection.php'); 

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'round'limit 1");
$stmt->execute();
$round = $stmt->get_result();
?>
