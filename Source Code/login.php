<?php
session_start();

// If user is already logged in, redirect to index page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

// Database connection
$conn = new mysqli("localhost", "root", "", "booknook");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST['user_id']);
    $password = trim($_POST['password']);

    // Validate input fields
    if (empty($user_id) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT user_id, password, email FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Direct comparison of passwords rather than using password_verify
            if ($password === $row['password']) {
                // Password is correct, start a new session
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['email'] = $row['email'];

                // Redirect to index page
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User ID not found!";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BookNook Inc</title>
    <link rel="stylesheet" href="login-styles.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <a href="index.php">📚 BookNook</a>
        </div>
    </header>

    <!-- Login Form -->
    <div class="auth-container">
        <div class="auth-form">
            <h2>Login</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <label for="user_id">User ID</label>
                    <input type="text" id="user_id" name="user_id" placeholder="Enter your user ID" required>
                </div>

                <div class="form-group password-toggle">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <span class="toggle-password" onclick="togglePasswordVisibility('password')">👁️</span>
                </div>

                <button type="submit" class="auth-btn">Login</button>
            </form>

            <div class="toggle-auth">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(id) {
            const passwordInput = document.getElementById(id);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }
    </script>
</body>

</html>