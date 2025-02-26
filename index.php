<?php
// db.php: Database connection setup
$host = 'localhost';
$db   = 'BOOK';
$user = 'root';
$pass = ''; // Replace with your MySQL password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if not exist
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Authentication</title>
    <!-- Include Font Awesome for the Eye Icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
    height: 100vh;
}

.container {
    background: #fff;
    padding: 40px 35px;
    border-radius: 12px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    transition: all 0.3s ease-in-out;
}

.container:hover {
    transform: scale(1.03);
}

h2 {
    text-align: center;
    font-size: 24px;
    color: #333;
    margin-bottom: 30px;
    font-weight: 600;
}

input {
    width: 100%;
    padding: 14px;
    margin: 10px 0;
    border: 2px solid #ddd;
    border-radius: 8px;
    background: #f9f9f9;
    font-size: 16px;
    color: #333;
    transition: 0.3s ease;
}

input:focus {
    border-color: #009688;
    background: #fff;
    outline: none;
}

button {
    width: 100%;
    padding: 14px;
    background: #ff9800;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
}

button:hover {
    background: #e68900;
}

a {
    display: block;
    text-align: center;
    margin-top: 15px;
    font-size: 16px;
    color: #009688;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s ease;
}

a:hover {
    color: #ff9800;
}

p {
    text-align: center;
    font-size: 14px;
    margin-top: 15px;
}

.alert {
    padding: 10px;
    margin-top: 20px;
    border-radius: 6px;
    font-weight: 500;
}

.alert.success {
    background-color: #2ecc71;
    color: #fff;
}

.alert.error {
    background-color: #e74c3c;
    color: #fff;
}

/* Eye icon styling */
.password-container {
    position: relative;
    width: 100%;
}

#eye-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 20px;
}
    </style>
</head>
<body>
<div class="container">
    <?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['register'])) {
            // Registration logic
            $email = $_POST['email'];
            $password = $_POST['password']; // No hashing

            $stmt = $conn->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
            $stmt->bind_param('ss', $email, $password);

            if ($stmt->execute()) {
                echo "<p style='color:green;'>Registration successful!</p>";
            } else {
                echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
            }
            $stmt->close();
        } elseif (isset($_POST['login'])) {
            // Login logic
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && $user['password'] === $password) { // Direct comparison without hashing
                $_SESSION['user'] = $user;
                header('Location: ?action=welcome');
                exit;
            } else {
                echo "<p style='color:red;'>Invalid email or password.</p>";
            }
            $stmt->close();
        } elseif (isset($_POST['forgot_password'])) {
            // Forgot password logic
            $email = $_POST['email'];
            $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $_SESSION['reset_email'] = $email;
                header('Location: ?action=reset_password');
                exit;
            } else {
                echo "<p style='color:red;'>No user found with that email address.</p>";
            }
            $stmt->close();
        } elseif (isset($_POST['reset_password'])) {
            // Reset password logic
            if (isset($_SESSION['reset_email'])) {
                $email = $_SESSION['reset_email'];
                $new_password = $_POST['new_password']; // No hashing

                $stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
                $stmt->bind_param('ss', $new_password, $email);

                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    unset($_SESSION['reset_email']);
                    echo "<p style='color:green;'>Password successfully reset!</p>";
                    header('Location: ?action=login');
                } else {
                    echo "<p style='color:red;'>Error: Could not reset password.</p>";
                }
                $stmt->close();
            } else {
                echo "<p style='color:red;'>Invalid reset attempt.</p>";
            }
        }
    }

    if (!isset($_GET['action']) || $_GET['action'] === 'login') {
    ?>
        <h2>Login</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span id="eye-icon" class="fa fa-eye" onclick="togglePassword()"></span>
            </div>
            
            <button type="submit" name="login">Login</button>
            <a href="?action=register">Don't have an account? Register</a>
            <a href="?action=forgot_password">Forgot Password?</a>
        </form>
    <?php
    } elseif ($_GET['action'] === 'register') {
    ?>
        <h2>Register</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span id="eye-icon" class="fa fa-eye" onclick="togglePassword()"></span>
            </div>
            <button type="submit" name="register">Register</button>
            <a href="?action=login">Already have an account? Login</a>
        </form>
    <?php
    } elseif ($_GET['action'] === 'forgot_password') {
    ?>
        <h2>Forgot Password</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="forgot_password">Submit</button>
            <a href="?action=login">Back to Login</a>
        </form>
    <?php
    } elseif ($_GET['action'] === 'reset_password' && isset($_SESSION['reset_email'])) {
    ?>
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit" name="reset_password">Reset Password</button>
            <a href="?action=login">Back to Login</a>
        </form>
    <?php
    } elseif ($_GET['action'] === 'welcome' && isset($_SESSION['user'])) {
    ?>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['email']); ?>!</h2>
        <a href="?action=logout">Logout</a>
    <?php
    } elseif ($_GET['action'] === 'logout') {
        session_destroy();
        header('Location: ?action=login');
        exit;
    }
    ?>
</div>

<!-- JavaScript for Password Toggle -->
<script>
    function togglePassword() {
        var passwordField = document.getElementById("password");
        var eyeIcon = document.getElementById("eye-icon");

        // Toggle the password field type
        if (passwordField.type === "password") {
            passwordField.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        }
    }
</script>
</body>
</html>
