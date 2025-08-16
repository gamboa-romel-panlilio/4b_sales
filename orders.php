<?php
require 'db.php';

// Handle new order from shop.php or admin form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $payment_method = $_POST['payment_method'] ?? 'Cash';

    if ($customer_name && $product_id) {
        // Check if customer exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE name=?");
        $stmt->bind_param("s", $customer_name);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $customer_id = $res->fetch_assoc()['id'];
        } else {
            // Insert new customer
            $stmt_insert = $conn->prepare("INSERT INTO customers (name) VALUES (?)");
            $stmt_insert->bind_param("s", $customer_name);
            $stmt_insert->execute();
            $customer_id = $stmt_insert->insert_id;
            $stmt_insert->close();
        }
        $stmt->close();

        // Always set new orders to Pending
        $status = 'Pending';

        // Insert order
        $stmt_order = $conn->prepare("INSERT INTO orders (customer_id, product_id, quantity, payment_method, status, order_date) VALUES (?,?,?,?,?,NOW())");
        $stmt_order->bind_param("iiiss", $customer_id, $product_id, $quantity, $payment_method, $status);
        $stmt_order->execute();
        $stmt_order->close();

        echo "<script>alert('Order placed successfully!'); window.location='orders.php';</script>";
        exit;
    }
}

// Handle delete order
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM orders WHERE id=$id");
    echo "<script>alert('Order deleted successfully!'); window.location='orders.php';</script>";
    exit;
}

// Fetch products
$products = $conn->query("SELECT * FROM products");

// Fetch orders with customer names
$orders = $conn->query("
    SELECT o.id, c.name AS customer_name, p.name AS product, o.quantity, o.order_date, o.payment_method, o.status
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="index.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
    <h1 class="mb-4 text-primary">📦 Orders Management</h1>

    <!-- Order Form -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">Place New Order</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" required>
                </div>
                <div class="col-md-3">
                    <select name="product_id" class="form-select" required>
                        <option value="">-- Select Product --</option>
                        <?php while($row = $products->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= $row['name'] ?> (₱<?= $row['price'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="quantity" class="form-control" placeholder="Qty" min="1" required>
                </div>
                <div class="col-md-2">
                    <select name="payment_method" class="form-select" required>
                        <option value="Cash">Cash</option>
                        <option value="GCash">GCash</option>
                        <option value="Card">Card</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_order" class="btn btn-success w-100">Add Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">All Orders</div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['customer_name'] ?></td>
                        <td><?= $row['product'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['order_date'] ?></td>
                        <td><span class="badge bg-info text-dark"><?= $row['payment_method'] ?></span></td>
                        <td>
                            <?php if ($row['status'] == 'Completed'): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php elseif ($row['status'] == 'Pending'): ?>
                                <!-- Link to order details for admin to approve -->
                                <a href="order_details.php?id=<?= $row['id'] ?>" class="badge bg-warning text-dark text-decoration-none">Pending</a>
                            <?php else: ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="orders.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this order?')">🗑 Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
