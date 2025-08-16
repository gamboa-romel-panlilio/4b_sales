<?php
require 'db.php';

// Fetch products
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background-color: #17568A;
        }
        .navbar-brand, .nav-link, .navbar-text {
            color: #fff !important;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-custom {
            background-color: #17568A;
            color: white;
            border-radius: 30px;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background-color: #0f3c63;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">4B Sales System</a>
        <div class="d-flex">
          <a href="index.php" class="btn btn-light btn-sm">Back to Dashboard</a>
        </div>
      </div>
    </nav>

    <!-- Shop Section -->
    <div class="container py-5">
        <h2 class="text-center mb-4 fw-bold">🛍️ Available Products</h2>
        <div class="row g-4">
            <?php while($row = $result->fetch_assoc()) { ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <!-- Show image from DB -->
                        <img src="<?= !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                             class="card-img-top" 
                             alt="<?= $row['name']; ?>" 
                             style="height:200px; object-fit:cover; border-top-left-radius:15px; border-top-right-radius:15px;">
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= $row['name']; ?></h5>
                            <p class="card-text text-muted">₱ <?= number_format($row['price'], 2); ?></p>
                            <p class="card-text"><small class="text-secondary">Stock: <?= $row['stock']; ?></small></p>
                            
                            <!-- Add to Cart Form -->
                            <form method="POST" action="orders.php">
                                <input type="hidden" name="product_id" value="<?= $row['id']; ?>">
                                <input type="hidden" name="product_name" value="<?= $row['name']; ?>">
                                <input type="hidden" name="price" value="<?= $row['price']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="<?= $row['stock']; ?>" class="form-control mb-2">
                                <button type="submit" name="add_to_cart" class="btn btn-custom w-100">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
