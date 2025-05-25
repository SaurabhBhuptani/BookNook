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

// Function to fetch books by genre
function getBooksByGenre($conn, $genre = null)
{
    $sql = "SELECT * FROM books";

    if ($genre === "Fiction" || $genre === "Mystery") {
        // For specific genres
        $sql .= " WHERE genre = '$genre'";
    } elseif ($genre === "Others") {
        // For other genres (not Fiction or Mystery)
        $sql .= " WHERE genre NOT IN ('Fiction', 'Mystery')";
    } elseif ($genre === "Featured") {
        // For featured books
        $sql .= " WHERE featured = 1";
    }

    $result = $conn->query($sql);
    $books = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }

    return $books;
}

// Fetch books by category
$featuredBooks = getBooksByGenre($conn, "Featured");
$fictionBooks = getBooksByGenre($conn, "Fiction");
$mysteryBooks = getBooksByGenre($conn, "Mystery");
$otherBooks = getBooksByGenre($conn, "Others");
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
    <!-- Header -->
    <header>
        <div class="logo">
            <h1>📚 BookNook</h1>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Search books, authors, genres...">
            <button>🔍</button>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="library.php">Library</a></li>
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

    <!-- Success notification -->
    <div id="notification" class="notification">
        <p id="notification-message"></p>
        <button id="close-notification">×</button>
    </div>

    <main>
        <!-- Welcome Section -->
        <section class="welcome">
            <h2>Welcome, <?php echo htmlspecialchars($user_id); ?>!</h2>
            <p>Explore our collection of books and find your next great read.</p>
        </section>

        <!-- Hero Section - Featured Books Carousel -->
        <section class="hero">
            <div class="hero-content" id="featured-carousel">
                <!-- This will be populated by JavaScript -->
            </div>
            <div class="hero-nav">
                <button onclick="prevHeroSlide()">❮</button>
                <button onclick="nextHeroSlide()">❯</button>
            </div>
        </section>

        <!-- Fiction Books Section -->
        <section class="book-section">
            <h2>Fiction</h2>
            <div class="book-grid">
                <?php foreach ($fictionBooks as $book): ?>
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
                                <button class="buy-button" data-bid="<?php echo $book['bid']; ?>" data-title="<?php echo htmlspecialchars($book['title']); ?>">
                                    <i class="fas fa-shopping-cart"></i> Buy Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Mystery Books Section -->
        <section class="book-section">
            <h2>Mystery</h2>
            <div class="book-grid">
                <?php foreach ($mysteryBooks as $book): ?>
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
                                <button class="buy-button" data-bid="<?php echo $book['bid']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Buy Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Other Genres Books Section -->
        <section class="book-section">
            <h2>Other Categories</h2>
            <div class="book-grid">
                <?php foreach ($otherBooks as $book): ?>
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
                                <button class="buy-button" data-bid="<?php echo $book['bid']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Buy Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
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
        document.addEventListener('DOMContentLoaded', function() {
            // Featured Books Data
            const featuredBooks = [
                <?php foreach ($featuredBooks as $book): ?> {
                        id: '<?php echo $book['bid']; ?>',
                        title: '<?php echo addslashes($book['title']); ?>',
                        author: '<?php echo addslashes($book['author']); ?>',
                        image: '<?php echo $book['p_location']; ?>',
                        description: '<?php echo addslashes(substr($book['description'], 0, 200)); ?>...'
                    },
                <?php endforeach; ?>
            ];

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

            // Notification system
            const notification = document.getElementById('notification');
            const notificationMessage = document.getElementById('notification-message');
            const closeNotification = document.getElementById('close-notification');

            function showNotification(message, isSuccess = true) {
                notificationMessage.textContent = message;
                notification.className = isSuccess ? 'notification success' : 'notification error';
                notification.style.display = 'block';

                // Auto hide after 3 seconds
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);

                // If success and it's a library addition, provide a link to library
                if (isSuccess && message.includes('library')) {
                    const viewLink = document.createElement('a');
                    viewLink.href = 'library.php';
                    viewLink.textContent = 'View in Library';
                    viewLink.className = 'notification-action';

                    // Clear existing action links and add the new one
                    const existingAction = notification.querySelector('.notification-action');
                    if (existingAction) notification.removeChild(existingAction);

                    notification.appendChild(viewLink);
                }
            }

            closeNotification.addEventListener('click', function() {
                notification.style.display = 'none';
            });

            // Buy Book functionality
            function buyBook(bookId, bookTitle = '') {
                fetch('purchase.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'bid=' + bookId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const title = bookTitle ? `"${bookTitle}"` : 'Book';
                            showNotification(`${title} added to your library!`);
                        } else {
                            showNotification(data.message, false);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred. Please try again.', false);
                    });
            }

            // Add event listeners to all "Buy Now" buttons
            document.querySelectorAll('.buy-button').forEach(button => {
                button.addEventListener('click', function() {
                    const bookId = this.getAttribute('data-bid');
                    const bookTitle = this.getAttribute('data-title') || '';
                    buyBook(bookId, bookTitle);
                });
            });

            // Hero Carousel
            let currentHeroIndex = 0;

            window.updateHeroSection = function() {
                if (featuredBooks.length === 0) {
                    document.querySelector('.hero').style.display = 'none';
                    return;
                }

                const book = featuredBooks[currentHeroIndex];
                const heroContent = document.getElementById('featured-carousel');

                heroContent.innerHTML = `
                <img src="${book.image}" alt="${book.title}" class="hero-image">
                <div class="hero-text">
                    <h1>${book.title}</h1>
                    <p class="author">By ${book.author}</p>
                    <p class="description">${book.description}</p>
                    <div class="cta-buttons">
                        <button class="btn-primary buy-button" data-bid="${book.id}" data-title="${book.title}">
                            <i class="fas fa-shopping-cart"></i> Buy Now
                        </button>
                    </div>
                </div>
            `;

                // Add click handlers to the new buttons
                document.querySelectorAll('.buy-button').forEach(button => {
                    button.addEventListener('click', function() {
                        const bookId = this.getAttribute('data-bid');
                        const bookTitle = this.getAttribute('data-title') || '';
                        buyBook(bookId, bookTitle);
                    });
                });
            };

            window.nextHeroSlide = function() {
                if (featuredBooks.length === 0) return;
                currentHeroIndex = (currentHeroIndex + 1) % featuredBooks.length;
                updateHeroSection();
            };

            window.prevHeroSlide = function() {
                if (featuredBooks.length === 0) return;
                currentHeroIndex = (currentHeroIndex - 1 + featuredBooks.length) % featuredBooks.length;
                updateHeroSection();
            };

            // Auto-advance hero carousel if there are featured books
            if (featuredBooks.length > 0) {
                setInterval(nextHeroSlide, 5000);
            }

            // Initialize Hero Section
            updateHeroSection();
        });
    </script>

</body>

</html>