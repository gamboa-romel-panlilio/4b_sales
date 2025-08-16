<?php
require 'db.php';

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $id = $_POST['product_id'] ?? null; // For edit
    $name = trim($_POST['product_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $image = $_FILES['image']['name'] ?? '';

    if (!empty($name) && $price > 0 && $stock >= 0) {
        // If editing
        if (!empty($id)) {
            // If new image uploaded
            if (!empty($image)) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $targetFile = $targetDir . time() . "_" . basename($image);
                move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

                // Delete old image
                $old = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
                if ($old && file_exists($old['image'])) unlink($old['image']);

                $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, image=? WHERE id=?");
                $stmt->bind_param("sdisi", $name, $price, $stock, $targetFile, $id);
            } else {
                $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=? WHERE id=?");
                $stmt->bind_param("sdii", $name, $price, $stock, $id);
            }
            $stmt->execute();
            $stmt->close();
            $message = "✅ Product updated successfully!";
        } else {
            // New product
            if (!empty($image)) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $targetFile = $targetDir . time() . "_" . basename($image);
                move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

                $stmt = $conn->prepare("INSERT INTO products (name, price, stock, image) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sdis", $name, $price, $stock, $targetFile);
                $stmt->execute();
                $stmt->close();
                $message = "✅ Product <b>" . htmlspecialchars($name) . "</b> added successfully!";
            } else {
                $error = "⚠ Please provide a product image.";
            }
        }
    } else {
        $error = "⚠ Please provide valid product details and stock.";
    }
}

// Handle delete product
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $result = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
    if ($result && file_exists($result['image'])) unlink($result['image']);
    $conn->query("DELETE FROM products WHERE id=$id");
    $message = "🗑 Product deleted successfully!";
}

// Fetch single product for edit
$editProduct = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $editProduct = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
}

// Fetch all products
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f4f6f9; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
        h2 { color: #17568A; }
        .message { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 5px; }
        .error { padding: 10px; background: #f8d7da; color: #721c24; margin-bottom: 15px; border-radius: 5px; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 8px; margin-bottom: 20px; background: #fafafa; }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #17568A; color: white; border: none; cursor: pointer; }
        button:hover { background: #0f3b5c; }
        .back-btn { display: inline-block; margin-bottom: 15px; padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; }
        .back-btn:hover { background: #545b62; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; vertical-align: middle; }
        th { background: #17568A; color: white; }
        img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .delete-btn { background: #dc3545; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; }
        .delete-btn:hover { background: #a71d2a; }
        .edit-btn { background: #ffc107; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; }
        .edit-btn:hover { background: #e0a800; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="back-btn">← Back to Dashboard</a>
    <h2><?= $editProduct ? 'Edit Product' : 'Add New Product'; ?></h2>

    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php if ($editProduct): ?>
            <input type="hidden" name="product_id" value="<?= $editProduct['id']; ?>">
        <?php endif; ?>
        <label>Product Name</label>
        <input type="text" name="product_name" value="<?= $editProduct['name'] ?? ''; ?>" required>

        <label>Price (₱)</label>
        <input type="number" name="price" step="0.01" min="1" value="<?= $editProduct['price'] ?? ''; ?>" required>

        <label>Stock</label>
        <input type="number" name="stock" min="0" value="<?= $editProduct['stock'] ?? '0'; ?>" required>

        <label>Product Image <?= $editProduct ? '(leave empty to keep existing)' : ''; ?></label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" name="add_product"><?= $editProduct ? 'Update Product' : 'Add Product'; ?></button>
    </form>

    <h2>Product List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Product</th>
            <th>Price (₱)</th>
            <th>Stock</th>
            <th>Date Added</th>
            <th>Action</th>
        </tr>
        <?php if ($products->num_rows > 0): ?>
            <?php while($row = $products->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['image']); ?>" alt="Product"></td>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td>₱<?= number_format($row['price'], 2); ?></td>
                <td><?= $row['stock']; ?></td>
                <td><?= $row['created_at'] ?? ''; ?></td>
                <td>
                    <a href="?edit=<?= $row['id']; ?>" class="edit-btn">Edit</a>
                    <a href="?delete=<?= $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No products yet.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
