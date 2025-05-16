<?php
include('connection.php'); 

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'officechair' limit 1");
$stmt->execute();
$officechair = $stmt->get_result();
?>
