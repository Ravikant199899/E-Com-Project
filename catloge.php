<?php
// Database connection setup
$host = 'localhost';
$db   = 'book';
$user = 'root';
$pass = ''; // Replace with your MySQL password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search functionality
$searchQuery = "";
$searchTerm = "";
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search_term']);
    $searchQuery = "WHERE title LIKE '%$searchTerm%' 
                    OR author LIKE '%$searchTerm%' 
                    OR genre LIKE '%$searchTerm%' 
                    OR year LIKE '%$searchTerm%'";
}

$books = [];
$result = $conn->query("SELECT * FROM books $searchQuery ORDER BY genre, title");
if ($result) {
    $books = $result->fetch_all(MYSQLI_ASSOC);
}
$booksByGenre = [];
foreach ($books as $book) {
    $booksByGenre[$book['genre']][] = $book;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Catalog</title>
    <style>
        /* General Reset */
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
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.9); /* Slight transparency */
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 1000px;
            transition: all 0.3s ease-in-out;
            text-align: center;
        }

        h2 {
            font-size: 26px;
            color: #333;
            margin-bottom: 15px;
        }

        input {
            width: 75%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 15px;
            background: #ff9800;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #e68900;
        }

        .genre-header {
            font-size: 22px;
            font-weight: bold;
            margin-top: 25px;
            text-align: left;
            padding-bottom: 5px;
            border-bottom: 2px solid #ddd;
        }

        .book-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 20px;
            margin-top: 15px;
        }

        .book-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            padding: 15px;
            width: 230px;
            text-align: left;
            transition: transform 0.3s ease-in-out;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .book-cover img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
        }

        .book-details {
            margin-top: 10px;
        }

        .book-detail {
            font-size: 14px;
            margin: 4px 0;
            color: #444;
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Book Catalog</h2>

    <!-- Search Form -->
    <form method="GET">
        <input type="text" name="search_term" placeholder="Search books by title, author, genre, or year" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit" name="search">Search</button>
    </form>

    <!-- Book List Section -->
    <h3 style="margin-top: 20px;">Book List</h3>
    <?php if (empty($books)): ?>
        <p>No books found.</p>
    <?php else: ?>
        <?php foreach ($booksByGenre as $genre => $books): ?>
            <div class="genre-section">
                <div class="genre-header"><?php echo htmlspecialchars($genre ?: 'Uncategorized'); ?></div>
                <div class="book-grid">
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <div class="book-cover">
                                <?php if ($book['cover_image']): ?>
                                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover Image">
                                <?php else: ?>
                                    <span>No Image</span>
                                <?php endif; ?>
                            </div>
                            <div class="book-details">
                                <div class="book-detail"><strong>Title:</strong> <?php echo htmlspecialchars($book['title']); ?></div>
                                <div class="book-detail"><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></div>
                                <div class="book-detail"><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre'] ?: 'N/A'); ?></div>
                                <div class="book-detail"><strong>Year:</strong> <?php echo htmlspecialchars($book['year']); ?></div>
                                <div class="book-detail"><strong>Price:</strong> ₹<?php echo htmlspecialchars(number_format($book['price'], 2)); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
