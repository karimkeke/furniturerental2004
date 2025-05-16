<?php
include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'accentchair' limit 1");
$stmt->execute();
$accentchair = $stmt->get_result();
?>
