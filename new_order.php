<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer = $_POST['customer'];
    $amount = $_POST['amount'];

    // Insert new customer if not exists
    $conn->query("INSERT INTO customers (name) VALUES ('$customer')");
    $customer_id = $conn->insert_id;

    // Insert order
    $conn->query("INSERT INTO orders (customer_id, amount, status) VALUES ($customer_id, $amount, 'Pending')");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>New Order</title></head>
<body>
<h2>Add New Order</h2>
<form method="post">
    Customer Name: <input type="text" name="customer" required><br><br>
    Amount: <input type="number" step="0.01" name="amount" required><br><br>
    <button type="submit">Save Order</button>
</form>
</body>
</html>
