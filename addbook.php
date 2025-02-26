<?php

$host = 'localhost';
$db   = 'book';
$user = 'root';
$pass = ''; 
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$createTableQuery = "
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    year INT,
    price DECIMAL(10, 2), -- New column for price
    cover_image VARCHAR(255) -- Column for cover image path
)";
if (!$conn->query($createTableQuery)) {
    die("Error creating table: " . $conn->error);
}
if(!$conn->query("SELECT * FROM books")){
    $conn->query("INSERT INTO `books` (`id`, `title`, `author`, `genre`, `year`, `price`, `cover_image`) VALUES (NULL, 'Programming in ANCI C', 'E Balagurusamy', 'Programming', '2024', '700.00', 'uploads/71wFVorFuaL._SY466_.jpg');");
    $conn->query("INSERT INTO `books` (`id`, `title`, `author`, `genre`, `year`, `price`, `cover_image`) VALUES (NULL, 'Berserk Vol 1', 'Kentaro Miura', 'Dark Fantasy', '1990', '800.00', 'uploads/71lnvXSiITL._SY466_.jpg');");
    $conn->query("INSERT INTO `books` (`id`, `title`, `author`, `genre`, `year`, `price`, `cover_image`) VALUES (NULL, 'Twisted Love', 'Ana Huang', 'Romance Novel', '2021', '360.00', 'uploads/71YJet75sxL._AC_UF1000,1000_QL80_.jpg');");
    $conn->query("INSERT INTO `books` (`id`, `title`, `author`, `genre`, `year`, `price`, `cover_image`) VALUES (NULL, 'Atomic Habits', 'James Clear', 'Self-Help Book', '2018', '460.00', 'uploads/81F90H7hnML.jpg');");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $year = $_POST['year'];
    $price = $_POST['price'];


    $coverImagePath = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $coverImagePath = $uploadDir . basename($_FILES['cover_image']['name']);
        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverImagePath)) {
            $coverImagePath = null; 
        }
    }

    $stmt = $conn->prepare('INSERT INTO books (title, author, genre, year, price, cover_image) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssids', $title, $author, $genre, $year, $price, $coverImagePath);
    if (!$stmt->execute()) {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$books = [];
$result = $conn->query("SELECT * FROM books");
if ($result) {
    $books = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADD BOOK</title>
    <style>

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
        height: 100vh;
        color: #333;
    }

    .container {
        background: #fff;
        padding: 40px 35px;
        border-radius: 12px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 1000px;
        transition: all 0.3s ease-in-out;
    }

    .container:hover {
        transform: scale(1.03);
    }

    h2 {
        text-align: center;
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 30px;
        font-weight: 600;
    }

    /* Form Styles */
    form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-gap: 20px;
        margin-bottom: 30px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: 1.1rem;
        color: #ff9800;
        margin-bottom: 8px;
    }

    .form-group input,
    .form-group button {
        padding: 14px;
        margin: 10px 0;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        color: #333;
        background: #f9f9f9;
        transition: 0.3s ease;
    }

    .form-group input:focus {
        border-color: #ff9800;
        background: #fff;
        outline: none;
    }

    .form-group button {
        width: 100%;
        background:  #ff9800;
        color: white;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .form-group button:hover {
        background:  #ff9800;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container {
            padding: 20px;
        }

        form {
            grid-template-columns: 1fr;
        }

        .form-group input,
        .form-group button {
            font-size: 0.9rem;
            padding: 12px;
        }
    }
    </style>
</head>
<body>

<div class="container">
    <h2>ADD BOOK</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title:</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Author:</label>
            <input type="text" name="author" required>
        </div>
        <div class="form-group">
            <label>Genre:</label>
            <input type="text" name="genre" required>
        </div>
        <div class="form-group">
            <label>Year:</label>
            <input type="number" name="year">
        </div>
        <div class="form-group">
            <label>Price:</label>
            <input type="number" step="0.01" name="price" required>
        </div>
        <div class="form-group">
            <label>Cover Image:</label>
            <input type="file" name="cover_image" accept="image/*">
        </div>
        <div class="form-group">
            <button type="submit" name="add_book">ADD BOOK</button>
        </div>
    </form>
</div>

</body>
</html>