<?php
include('connection.php'); 

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'bedframe' limit 1");
$stmt->execute();
$bedframe = $stmt->get_result();
?>
