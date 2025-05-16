<?php
include('connection.php'); 

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'rectangle'limit 1");
$stmt->execute();
$rectangle = $stmt->get_result();
?>
