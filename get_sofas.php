<?php
include('connection.php'); 

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'sofas' limit 1");
$stmt->execute();
$sofas = $stmt->get_result();
?>
