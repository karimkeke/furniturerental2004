<?php
include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'coffeetables' limit 1");
$stmt->execute();
$coffeetables = $stmt->get_result();
?>
