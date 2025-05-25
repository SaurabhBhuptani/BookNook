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

// Check if book ID is provided
if (!isset($_GET['bid'])) {
    header("Location: library.php");
    exit();
}

$bid = $_GET['bid'];
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'] ?? '';

// Check if the book is in user's library and get book information from user's library table
$table_name = "user_" . $user_id . "_library";
$lib_sql = "SELECT * FROM $table_name WHERE bid = '$bid'";
$lib_result = $conn->query($lib_sql);

if ($lib_result->num_rows == 0) {
    // Book not in user's library
    header("Location: library.php");
    exit();
}

// Get book data from user's library table
$book = $lib_result->fetch_assoc();

// If title/author not available in user table, fall back to books table for metadata only
if (empty($book['title']) || empty($book['author'])) {
    $metadata_sql = "SELECT title, author FROM books WHERE bid = '$bid'";
    $metadata_result = $conn->query($metadata_sql);

    if ($metadata_result->num_rows > 0) {
        $metadata = $metadata_result->fetch_assoc();
        $book['title'] = $book['title'] ?: $metadata['title'];
        $book['author'] = $book['author'] ?: $metadata['author'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading: <?php echo $book['title']; ?> - BookNook</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="read.css">
</head>

<body>
    <header>
        <div class="logo">
            <h1>📚 BookNook</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
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

    <main>
        <section class="book-reader">
            <div class="reader-header">
                <h2><?php echo $book['title']; ?></h2>
                <p class="author">by <?php echo $book['author']; ?></p>
                <a href="library.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Library
                </a>
            </div>

            <div class="pdf-controls">
                <button id="prev" title="Previous Page">
                    <i class="fas fa-chevron-left"></i> Previous
                </button>

                <div class="page-info">
                    <span>Page</span>
                    <input type="number" id="page-num" min="1">
                    <span>of <span id="page-count"></span></span>
                </div>

                <button id="next" title="Next Page">
                    Next <i class="fas fa-chevron-right"></i>
                </button>

                <button id="zoom-in" title="Zoom In">
                    <i class="fas fa-search-plus"></i> Zoom In
                </button>

                <button id="zoom-out" title="Zoom Out">
                    <i class="fas fa-search-minus"></i> Zoom Out
                </button>
            </div>

            <div class="pdf-container">
                <canvas id="pdf-viewer"></canvas>
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

        // Get PDF path from user's library table and normalize it
        <?php
        $raw = $book['b_location'];
        // Convert backslashes to forward slashes for web URLs
        $fixed = str_replace('\\', '/', $raw);
        // Remove any duplicate slashes
        $fixed = preg_replace('#/+#', '/', $fixed);
        ?>
        const url = <?php echo json_encode($fixed); ?>;

        // The workerSrc property needs to be specified
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

        let pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 1.0,
            canvas = document.getElementById('pdf-viewer'),
            ctx = canvas.getContext('2d');

        /**
         * Get the document loaded and initial page displayed
         */
        pdfjsLib.getDocument({
            url: url,
            withCredentials: true
        }).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById('page-count').textContent = pdfDoc.numPages;

            // Set the max value for the page number input
            document.getElementById('page-num').max = pdfDoc.numPages;

            // Initial/first page rendering
            renderPage(pageNum);
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            document.querySelector('.pdf-container').innerHTML = '<div class="error-message"><p>Error loading PDF. Please verify the file exists at: ' + url + '</p></div>';
        });

        /**
         * Renders the specified page
         */
        function renderPage(num) {
            pageRendering = true;

            // Update page input field
            document.getElementById('page-num').value = num;

            // Using promise to fetch the page
            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({
                    scale: scale
                });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render PDF page into canvas context
                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };

                const renderTask = page.render(renderContext);

                // Wait for rendering to finish
                renderTask.promise.then(function() {
                    pageRendering = false;

                    if (pageNumPending !== null) {
                        // New page rendering is pending
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });
        }

        /**
         * If another page rendering in progress, wait until it's done.
         * Otherwise, execute rendering immediately.
         */
        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        /**
         * Go to previous page
         */
        function onPrevPage() {
            if (!pdfDoc || pageNum <= 1) {
                return;
            }
            pageNum--;
            queueRenderPage(pageNum);
        }
        document.getElementById('prev').addEventListener('click', onPrevPage);

        /**
         * Go to next page
         */
        function onNextPage() {
            if (!pdfDoc || pageNum >= pdfDoc.numPages) {
                return;
            }
            pageNum++;
            queueRenderPage(pageNum);
        }
        document.getElementById('next').addEventListener('click', onNextPage);

        /**
         * Go to a specific page when user inputs a page number
         */
        document.getElementById('page-num').addEventListener('change', function() {
            if (!pdfDoc) return;

            const num = parseInt(this.value);
            if (num > 0 && num <= pdfDoc.numPages) {
                pageNum = num;
                queueRenderPage(pageNum);
            } else {
                this.value = pageNum;
            }
        });

        /**
         * Zoom In
         */
        function zoomIn() {
            if (!pdfDoc) return;
            scale *= 1.25;
            queueRenderPage(pageNum);
        }
        document.getElementById('zoom-in').addEventListener('click', zoomIn);

        /**
         * Zoom Out
         */
        function zoomOut() {
            if (!pdfDoc) return;
            scale /= 1.25;
            queueRenderPage(pageNum);
        }
        document.getElementById('zoom-out').addEventListener('click', zoomOut);

        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (!pdfDoc) return;

            // Right arrow or Page Down for next page
            if (e.key === 'ArrowRight' || e.key === 'PageDown') {
                onNextPage();
            }
            // Left arrow or Page Up for previous page
            else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                onPrevPage();
            }
            // Plus key for zoom in
            else if (e.key === '+') {
                zoomIn();
            }
            // Minus key for zoom out
            else if (e.key === '-') {
                zoomOut();
            }
        });
    </script>
</body>

</html>