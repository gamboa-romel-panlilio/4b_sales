<?php
require 'db.php';

$id = $_GET['id'] ?? 0;

// Handle approve action
if (isset($_POST['approve'])) {
    $conn->query("UPDATE orders SET status='Completed' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Get order details
$order = $conn->query("
    SELECT orders.*, customers.name, customers.email, customers.phone, products.name AS product_name, products.price, orders.quantity
    FROM orders
    JOIN customers ON orders.customer_id = customers.id
    JOIN products ON orders.product_id = products.id
    WHERE orders.id = $id
")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order #<?= $order['id'] ?> Details</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7fb; margin:0; padding:0; }
        .container { max-width: 700px; margin: 50px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        h2 { color: #17568A; margin-bottom: 20px; }
        p { font-size: 16px; margin: 8px 0; }
        .product-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .product-table th, .product-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .product-table th { background-color: #17568A; color: white; }
        .status { font-weight: bold; padding: 6px 12px; border-radius: 6px; display: inline-block; }
        .status.pending { background-color: #ffc107; color: #333; }
        .status.completed { background-color: #28a745; color: white; }
        .btn { padding: 12px 25px; font-size: 16px; border: none; border-radius: 8px; cursor: pointer; margin-top: 20px; }
        .btn-approve { background-color: #28a745; color: white; }
        .btn-back { background-color: #6c757d; color: white; text-decoration: none; display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Order #<?= $order['id'] ?></h2>
    
    <p><b>Customer:</b> <?= htmlspecialchars($order['name']) ?></p>
    <p><b>Email:</b> <?= htmlspecialchars($order['email']) ?></p>
    <p><b>Phone:</b> <?= htmlspecialchars($order['phone']) ?></p>
    <p><b>Date:</b> <?= $order['order_date'] ?></p>
    <p><b>Status:</b> 
        <span class="status <?= strtolower($order['status']) ?>">
            <?= $order['status'] ?>
        </span>
    </p>

    <h3>Product Details</h3>
    <table class="product-table">
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        <tr>
            <td><?= htmlspecialchars($order['product_name']) ?></td>
            <td>₱<?= number_format($order['price'],2) ?></td>
            <td><?= $order['quantity'] ?></td>
            <td>₱<?= number_format($order['price'] * $order['quantity'],2) ?></td>
        </tr>
    </table>

    <!-- Approve Button if Pending -->
    <?php if ($order['status'] == 'Pending'): ?>
        <form method="POST">
            <button type="submit" name="approve" class="btn btn-approve">✅ Approve Order</button>
        </form>
    <?php endif; ?>

    <a href="index.php" class="btn-back">⬅ Back to Dashboard</a>
</div>
</body>
</html>
