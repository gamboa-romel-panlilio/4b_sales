<?php
session_start();
require 'db.php';

// Default role
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = "User";
}

// Toggle role (Admin/User)
if (isset($_GET['role'])) {
    $_SESSION['role'] = $_GET['role'];
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'];

// Stats (Admin only)
if ($role === "Admin") {
    $totalOrders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
    $pendingOrders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='Pending'")->fetch_assoc()['total'];
    $completedOrders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='Completed'")->fetch_assoc()['total'];

    // Calculate revenue dynamically
    $revenueRow = $conn->query("
        SELECT SUM(p.price * o.quantity) AS revenue
        FROM orders o
        JOIN products p ON o.product_id = p.id
    ")->fetch_assoc();
    $revenue = $revenueRow['revenue'] ?? 0;
}

// Recent Orders (Admin only)
if ($role === "Admin") {
    $orders = $conn->query("
        SELECT o.id, c.name AS customer_name, o.order_date, o.status, p.name AS product_name, p.price, o.quantity, (p.price * o.quantity) AS total_amount
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        JOIN products p ON o.product_id = p.id
        ORDER BY o.id DESC
        LIMIT 10
    ");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #f4f7fb; color: #333; }
        .top-bar { background: #17568A; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .top-bar h1 { margin: 0; font-size: 24px; }
        .role-btns a { text-decoration: none; padding: 8px 15px; border-radius: 6px; margin-left: 5px; font-size: 14px; }
        .admin-btn { background: #28a745; color: white; }
        .user-btn { background: #6c757d; color: white; }
        .container { padding: 30px; }
        .menu { display: flex; gap: 15px; margin-bottom: 20px; }
        .menu button { flex: 1; padding: 15px; border: none; border-radius: 10px; font-size: 16px; cursor: pointer; background: #fff; box-shadow: 0 3px 6px rgba(0,0,0,0.1); transition: 0.3s; }
        .menu button:hover { background: #17568A; color: white; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-box { padding: 20px; border-radius: 12px; background: white; text-align: center; font-size: 18px; font-weight: bold; box-shadow: 0 3px 8px rgba(0,0,0,0.1); transition: 0.3s; }
        .stat-box:hover { transform: translateY(-5px); }
        .stat-box span { display: block; font-size: 14px; font-weight: normal; color: #555; margin-bottom: 8px; }
        h2 { margin-top: 40px; color: #17568A; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; box-shadow: 0 3px 6px rgba(0,0,0,0.05); }
        table th { background: #17568A; color: white; padding: 12px; }
        table td { padding: 12px; border-bottom: 1px solid #ddd; vertical-align: middle; }
        table tr:hover { background: #f9f9f9; cursor: pointer; }
        .welcome-box { padding: 40px; text-align: center; background: white; border-radius: 12px; box-shadow: 0 3px 6px rgba(0,0,0,0.1); }
        .welcome-box h2 { margin-bottom: 20px; }
        .badge { padding: 4px 8px; border-radius: 6px; color: white; font-size: 14px; font-weight: bold; display: inline-block; text-decoration: none; }
        .badge.completed { background-color: #28a745; }
        .badge.pending { background-color: #ffc107; color: #333; }
        .badge.cancelled { background-color: #dc3545; }
        td.amount { text-align: right; }
    </style>
</head>
<body>

<div class="top-bar">
    <h1>📊 Sales Dashboard</h1>
    <div class="role-btns">
        <span><b>Role:</b> <?= htmlspecialchars($role) ?></span>
        <a href="?role=Admin" class="admin-btn">Admin</a>
        <a href="?role=User" class="user-btn">User</a>
    </div>
</div>

<div class="container">

    <!-- Menu -->
    <div class="menu">
        <?php if ($role === "Admin"): ?>
            <button onclick="location.href='orders.php'">🛒 Orders</button>
            <button onclick="location.href='invoices.php'">📄 Invoices</button>
            <button onclick="location.href='customers.php'">👥 Customers</button>
            <button onclick="location.href='products.php'">➕ Add Product</button>
        <?php else: ?>
            <button onclick="location.href='shop.php'">🛍 Shop</button>
            <button onclick="location.href='orders.php'">📦 My Orders</button>
        <?php endif; ?>
    </div>

    <!-- Stats (only Admin can see) -->
    <?php if ($role === "Admin"): ?>
    <div class="stats">
        <div class="stat-box">
            <span>Total Orders</span>
            <?= $totalOrders ?>
        </div>
        <div class="stat-box">
            <span>Pending Orders</span>
            <?= $pendingOrders ?>
        </div>
        <div class="stat-box">
            <span>Completed</span>
            <?= $completedOrders ?>
        </div>
        <div class="stat-box">
            <span>Revenue</span>
            ₱<?= number_format($revenue,2) ?>
        </div>
    </div>
    <?php else: ?>
    <!-- User welcome box -->
    <div class="welcome-box">
        <h2>👋 Welcome to the Sales System</h2>
        <p>As a <b>User</b>, you can browse our shop and track your orders.</p>
        <button onclick="location.href='shop.php'">Go to Shop 🛍</button>
    </div>
    <?php endif; ?>

    <!-- Recent Orders (Admin only) -->
    <?php if ($role === "Admin"): ?>
   <h2>🕒 Recent Orders</h2>
<table>
    <thead>
        <tr>
            <th style="text-align:center;">Order ID</th>
            <th style="text-align:left;">Customer</th>
            <th style="text-align:left;">Date</th>
            <th style="text-align:right;">Amount</th>
            <th style="text-align:center;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $orders->fetch_assoc()): ?>
        <tr>
            <td style="text-align:center;"><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= $row['order_date'] ?></td>
            <td style="text-align:right;">₱<?= number_format($row['total_amount'],2) ?></td>
            <td style="text-align:center;">
                <?php $orderId = $row['id']; ?>
                <?php if ($row['status'] == 'Completed'): ?>
                    <span class="badge completed">Completed</span>
                <?php elseif ($row['status'] == 'Pending'): ?>
                    <a href="order_details.php?id=<?= $orderId ?>" class="badge pending">Pending</a>
                <?php else: ?>
                    <span class="badge cancelled">Cancelled</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

    <?php endif; ?>

</div>

</body>
</html>
