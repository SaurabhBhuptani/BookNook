<?php
session_start();

// If user is already logged in, redirect to index page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

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
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);

    // Validate input fields
    if (empty($user_id) || empty($password) || empty($confirm_password) || empty($email)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 8 || strlen($password) > 20) {
        $error = "Password must be between 8 and 20 characters!";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = "Password must contain at least one uppercase letter, one number, and one special character!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if user_id already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "User ID already exists!";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email already exists!";
            } else {
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (user_id, password, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $user_id, $password, $email);

                if ($stmt->execute()) {
                    $success = "Registration successful! Please login.";
                } else {
                    $error = "Error: " . $stmt->error;
                }
            }
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
    <title>Register - BookNook Inc</title>
    <link rel="stylesheet" href="register-styles.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <a href="index.php">📚 BookNook</a>
        </div>
    </header>

    <!-- Registration Form -->
    <div class="auth-container">
        <div class="auth-form">
            <h2>Create Account</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
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
                    <div class="password-requirements">
                        Password must be 8-20 characters and include at least one uppercase letter, one number, and one special character.
                    </div>
                </div>

                <div class="form-group password-toggle">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">👁️</span>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <button type="submit" class="auth-btn">Register</button>
            </form>

            <div class="toggle-auth">
                Already have an account? <a href="login.php">Login here</a>
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