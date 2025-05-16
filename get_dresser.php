<?php
include('connection.php'); 

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'dresser' limit 1");
$stmt->execute();
$dresser = $stmt->get_result();
?>
