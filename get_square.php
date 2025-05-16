<?php
include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'square'limit 1");
$stmt->execute();
$square = $stmt->get_result();
?>
