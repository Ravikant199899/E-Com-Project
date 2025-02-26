<?php

$host = 'localhost';
$db   = 'BOOK';  // Your database name
$user = 'root';  // Your database username
$pass = '';      // Your database password

// Create database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch books from the database
$result = $conn->query("SELECT * FROM books");

// Close the connection
$conn->close();
?>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">BookStore</div>
    <div class="nav-links">
        <a href="index.php?action=login">Login</a>
        <a href="index.php?action=register">Register</a>
        <a href="cart.php?action=cart">Cart</a>
    </div>
</nav>

<?php
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'login') {
        header("Location: login.php"); // Redirect to login page
        exit();
    } elseif ($action === 'register') {
        header("Location: register.php"); // Redirect to register page
        exit();
    } elseif ($action === 'cart') {
        header("Location: cart.php"); // Redirect to cart page
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Book Store</title>
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f9f9f9;
            color: #333;
        }

        h3 {
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: 600;
        }

        /* Navbar Styles */
        .navbar {
            display: flex;
            justify-content: space-between;
            padding: 15px 30px;
            background-color: #6e7dff;
            color: white;
            align-items: center;
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: 700;
        }

        .navbar .nav-links a {
            margin: 0 15px;
            text-decoration: none;
            color: white;
            font-size: 16px;
            font-weight: 500;
            transition: 0.3s;
        }

        .navbar .nav-links a:hover {
            color: #ff6584;
        }

        /* Search Section */
        .search-section {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }

        #search-bar {
            width: 70%;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 25px;
            border: 2px solid #ddd;
            transition: 0.3s;
            outline: none;
        }

        #search-bar:focus {
            border-color: #6e7dff;
        }

        /* Book Listing */
        .books-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .book-card {
            background-color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .book-card:hover {
            transform: translateY(-10px);
        }

        .book-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .book-info {
            text-align: center;
        }

        .price {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .btn-add-to-cart {
            padding: 10px 15px;
            background-color: #6e7dff;
            color: white;
            font-size: 16px;
            font-weight: 500;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-add-to-cart:hover {
            background-color: #5a6fd5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .books-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            #search-bar {
                width: 80%;
            }
        }
    </style>
</head>
<body>

    <!-- Search Bar -->
    <section class="search-section">
        <input type="text" id="search-bar" placeholder="Search for books..." onkeyup="searchBooks()">
    </section>

    <!-- Book List -->
    <section class="books-container" id="book-list">
        <?php if ($result->num_rows > 0) {
            while ($book = $result->fetch_assoc()) { ?>
                <div class="book-card">
                    <img src="<?php echo $book['image']; ?>" alt="<?php echo $book['title']; ?>" class="book-image">
                    <div class="book-info">
                        <h3><?php echo $book['title']; ?></h3>
                        <p><?php echo $book['author']; ?></p>
                        <p class="price">$<?php echo $book['price']; ?></p>
                        
                        <!-- Add to Cart Form -->
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <input type="hidden" name="title" value="<?php echo $book['title']; ?>">
                            <input type="hidden" name="author" value="<?php echo $book['author']; ?>">
                            <input type="hidden" name="price" value="<?php echo $book['price']; ?>">
                            <input type="hidden" name="image" value="<?php echo $book['image']; ?>">
                            <button type="submit" class="btn-add-to-cart">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php }
        } else {
            echo "<p>No books available.</p>";
        } ?>
    </section>

    <script>
        // Search functionality for books
        function searchBooks() {
            let query = document.getElementById('search-bar').value.toLowerCase();
            let books = document.querySelectorAll('.book-card');

            books.forEach(book => {
                let title = book.querySelector('h3').textContent.toLowerCase();
                let author = book.querySelector('p').textContent.toLowerCase();

                if (title.includes(query) || author.includes(query)) {
                    book.style.display = 'block';
                } else {
                    book.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
