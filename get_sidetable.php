<?php
include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'sidetable' limit 1");
$stmt->execute();
$sidetable = $stmt->get_result();
?>
