<?php
include('connection.php'); 

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'cornerdesk' limit 1");
$stmt->execute();
$cornerdesk = $stmt->get_result();
?>
