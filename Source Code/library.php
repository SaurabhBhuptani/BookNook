<?php
// Start session to manage user data
session_start();

// Include database connection
include_once "db_connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'] ?? '';

// Function to fetch user's library books
function getUserLibrary($conn, $user_id)
{
    // Check if the user's library table exists
    $table_name = "user_" . $user_id . "_library";

    // Query to check if table exists
    $check_table = "SHOW TABLES LIKE '$table_name'";
    $result = $conn->query($check_table);

    if ($result->num_rows > 0) {
        // Table exists, fetch the books
        $sql = "SELECT * FROM $table_name";
        $result = $conn->query($sql);

        $books = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
        }
        return $books;
    } else {
        // Table doesn't exist yet
        return [];
    }
}

// Get user's library books
$libraryBooks = getUserLibrary($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookNook Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <div class="logo">
            <h1>📚 BookNook</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="library.php" class="active">Library</a></li>
                <li><a href="upload_page.php">User Upload</a></li>
                <!-- User Profile -->
                <li>
                    <div class="user-profile" id="userProfile">
                        <div class="user-info">
                            <div class="user-avatar"><?php echo strtoupper(substr($user_id, 0, 1)); ?></div>
                            <span class="user-name"><?php echo htmlspecialchars($user_id); ?></span>
                        </div>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-item">
                                <strong><?php echo htmlspecialchars($user_id); ?></strong>
                                <div><?php echo htmlspecialchars($email); ?></div>
                            </div>
                            <a href="logout.php" class="dropdown-item logout">Logout</a>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="welcome">
            <h2>My Library</h2>
            <p>Access all your purchased books here.</p>
        </section>

        <section class="book-section">
            <div class="book-grid">
                <?php if (empty($libraryBooks)): ?>
                    <div class="empty-library">
                        <p>Your library is empty. Purchase books to add them to your library.</p>
                        <a href="index.php" class="btn">Browse Books</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($libraryBooks as $book): ?>
                        <div class="book-card">
                            <div class="book-image">
                                <img src="<?php echo $book['p_location']; ?>" alt="<?php echo $book['title']; ?>">
                            </div>
                            <div class="book-info">
                                <h3><?php echo $book['title']; ?></h3>
                                <p class="author">by <?php echo $book['author']; ?></p>
                                <p class="genre"><?php echo $book['genre']; ?></p>
                                <p class="description"><?php echo substr($book['description'], 0, 100); ?>...</p>
                                <div class="book-actions">
                                    <a href="read.php?bid=<?php echo $book['bid']; ?>" class="btn read-book">
                                        <i class="fas fa-book-open"></i> Read
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="#">About</a>
                <a href="#">FAQ</a>
                <a href="#">Contact</a>
                <a href="#">Privacy Policy</a>
            </div>
            <div class="footer-section">
                <h3>Categories</h3>
                <a href="#">Fiction</a>
                <a href="#">Mystery</a>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i> Instagram</a>
                    <a href="#"><i class="fab fa-twitter"></i> Twitter</a>
                    <a href="#"><i class="fab fa-facebook"></i> Facebook</a>
                    <a href="#"><i class="fab fa-youtube"></i> YouTube</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // User Profile Dropdown
        const userProfile = document.getElementById('userProfile');
        const userDropdown = document.getElementById('userDropdown');

        userProfile.addEventListener('click', function(event) {
            userDropdown.classList.toggle('active');
            event.stopPropagation();
        });

        // Close dropdown when clicking elsewhere
        document.addEventListener('click', function() {
            userDropdown.classList.remove('active');
        });
    </script>

</body>

</html>