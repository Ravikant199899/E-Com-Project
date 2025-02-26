<?php
session_start();

// Check if the cart exists in the session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $message = "Your cart is empty.";
} else {
    $message = null;
}

// Database connection setup
$host = 'localhost';
$db   = 'book';
$user = 'root';
$pass = ''; // Replace with your MySQL password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve book details for the cart
$cartBooks = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $bookIds = implode(',', array_keys($_SESSION['cart']));
    $result = $conn->query("SELECT * FROM books WHERE id IN ($bookIds)");

    if ($result) {
        $cartBooks = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Handle removing a book from the cart
if (isset($_GET['remove'])) {
    $removeBookId = (int)$_GET['remove'];

    // Remove the book from the cart session
    if (isset($_SESSION['cart'][$removeBookId])) {
        unset($_SESSION['cart'][$removeBookId]);
    }

    // Redirect back to cart page after removal
    header("Location: cart.php");
    exit();
}

// Handle increasing the quantity
if (isset($_GET['increase'])) {
    $bookId = (int)$_GET['increase'];
    if (isset($_SESSION['cart'][$bookId])) {
        $_SESSION['cart'][$bookId]++;
    }
    header("Location: cart.php");
    exit();
}

// Handle decreasing the quantity
if (isset($_GET['decrease'])) {
    $bookId = (int)$_GET['decrease'];
    if (isset($_SESSION['cart'][$bookId]) && $_SESSION['cart'][$bookId] > 1) {
        $_SESSION['cart'][$bookId]--;
    }
    header("Location: cart.php");
    exit();
}

// Handle clearing the entire cart
if (isset($_GET['clear_cart'])) {
    // Clear the cart
    unset($_SESSION['cart']);
    // Redirect back to cart page after clearing
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #009688, #ff9800);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .cart-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            transition: all 0.3s ease-in-out;
        }

        .cart-container:hover {
            transform: scale(1.03);
        }

        h2 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .cart-items {
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }

        .cart-item img {
            max-width: 100px;
            max-height: 150px;
            border-radius: 8px;
        }

        .cart-item-details {
            flex-grow: 1;
            padding-left: 15px;
        }

        .cart-item-details p {
            margin: 5px 0;
        }

        .remove-btn {
            background-color: #ff9800;
            color: white;
            padding: 5px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .remove-btn:hover {
            background-color: #ff9800;
        }

        .quantity-btn {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn a {
            background-color: #ff9800;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 1rem;
            text-decoration: none;
        }

        .quantity-btn a:hover {
            background-color:#ff9800;
        }

        .clear-cart-btn, .checkout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #ff9800;
            color: white;
            font-size: 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .clear-cart-btn:hover, .checkout-btn:hover {
            background-color:#ff9800;
        }

        .cart-summary {
            margin-top: 30px;
            font-size: 1.2rem;
            text-align: center;
        }

        .cart-summary p {
            font-weight: bold;
        }

        .alert {
            padding: 10px;
            margin-top: 20px;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert.success {
            background-color:#ff9800;
            color: #fff;
        }

        .alert.error {
            background-color:#ff9800;
            color: #fff;
        }

    </style>
</head>
<body>

<div class="cart-container">
    <h2>Your Cart</h2>

    <?php if ($message): ?>
        <p style="text-align: center; color: #ff9800;"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (!empty($cartBooks)): ?>
        <div class="cart-items">
            <?php
            $totalPrice = 0;
            foreach ($cartBooks as $book):
                $bookId = $book['id'];
                $quantity = $_SESSION['cart'][$bookId];
                $totalPrice += $book['price'] * $quantity;
            ?>
                <div class="cart-item">
                    <div class="cart-item-img">
                        <?php if ($book['cover_image']): ?>
                            <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover Image">
                        <?php else: ?>
                            <span>No Image</span>
                        <?php endif; ?>
                    </div>
                    <div class="cart-item-details">
                        <p><strong>Title:</strong> <?php echo htmlspecialchars($book['title']); ?></p>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                        <p><strong>Price:</strong> ₹<?php echo number_format($book['price'], 2); ?></p>
                        <div class="quantity-btn">
                            <a href="?decrease=<?php echo $bookId; ?>">-</a>
                            <span>Quantity: <?php echo $quantity; ?></span>
                            <a href="?increase=<?php echo $bookId; ?>">+</a>
                        </div>
                        <p><strong>Total:</strong> ₹<?php echo number_format($book['price'] * $quantity, 2); ?></p>
                    </div>
                    <a href="?remove=<?php echo $bookId; ?>" class="remove-btn">Remove</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <p><strong>Total Price:</strong> ₹<?php echo number_format($totalPrice, 2); ?></p>
            <a href="?clear_cart=true" class="clear-cart-btn">Clear Cart</a>
            <a href="#" class="checkout-btn">Proceed to Checkout</a>
        </div>

    <?php else: ?>
        <p style="text-align: center;">Your cart is empty. <a href="catloge.php">Browse Books</a></p>
    <?php endif; ?>

</div>

</body>
</html>
