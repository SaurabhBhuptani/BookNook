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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file']) && isset($_FILES['cover_image'])) {
    $title = trim($_POST['title']);
    $pdf_file = $_FILES['pdf_file'];
    $cover_file = $_FILES['cover_image'];

    // Validation
    $errors = [];

    if (empty($title)) {
        $errors[] = "Title is required";
    }

    // Validate PDF file
    if ($pdf_file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "PDF file upload failed";
    } elseif ($pdf_file['type'] !== 'application/pdf') {
        $errors[] = "Only PDF files are allowed";
    }

    // Validate cover image
    $allowed_image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if ($cover_file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Cover image upload failed";
    } elseif (!in_array($cover_file['type'], $allowed_image_types)) {
        $errors[] = "Cover image must be JPEG, PNG, or GIF";
    }

    if (empty($errors)) {
        // Get the next available bid and pid
        $table_name = "user_" . $user_id . "_library";
        $bid_query = "SELECT MAX(CAST(bid AS UNSIGNED)) as max_bid FROM $table_name";
        $bid_result = $conn->query($bid_query);
        $max_bid = $bid_result->fetch_assoc()['max_bid'] ?? 0;

        // For user uploads, start bid with 1 instead of 0
        if ($max_bid < 100) {
            $new_bid = '101';
        } else {
            $new_bid = str_pad($max_bid + 1, 3, '0', STR_PAD_LEFT);
        }

        $pid_query = "SELECT MAX(CAST(pid AS UNSIGNED)) as max_pid FROM $table_name";
        $pid_result = $conn->query($pid_query);
        $max_pid = $pid_result->fetch_assoc()['max_pid'] ?? 0;
        $new_pid = str_pad($max_pid + 1, 3, '0', STR_PAD_LEFT);

        // Create user directory structure
        $base_path = "E:\\XAMPP\\htdocs\\Project\\";
        $user_folder = $base_path . $user_id;
        $cover_folder = $user_folder . "\\cover";
        $pdf_folder = $user_folder . "\\pdf";

        // Create directories if they don't exist
        if (!file_exists($user_folder)) {
            mkdir($user_folder, 0777, true);
        }
        if (!file_exists($cover_folder)) {
            mkdir($cover_folder, 0777, true);
        }
        if (!file_exists($pdf_folder)) {
            mkdir($pdf_folder, 0777, true);
        }

        // Get file extensions
        $pdf_extension = pathinfo($pdf_file['name'], PATHINFO_EXTENSION);
        $cover_extension = pathinfo($cover_file['name'], PATHINFO_EXTENSION);

        // Define file paths
        $pdf_filename = $new_bid . '.' . $pdf_extension;
        $cover_filename = $new_pid . '.' . $cover_extension;

        $pdf_path = $pdf_folder . "\\" . $pdf_filename;
        $cover_path = $cover_folder . "\\" . $cover_filename;

        // Move uploaded files
        if (
            move_uploaded_file($pdf_file['tmp_name'], $pdf_path) &&
            move_uploaded_file($cover_file['tmp_name'], $cover_path)
        ) {

            // Store relative paths for web access
            $p_location_web = $user_id . "/cover/" . $cover_filename;
            $b_location_web = $user_id . "/pdf/" . $pdf_filename;

            // Insert into books table
            $title_escaped = $conn->real_escape_string($title);
            $insert_sql = "INSERT INTO books (bid, pid, p_location, b_location, title, author, description, genre, featured) 
                          VALUES ('$new_bid', '$new_pid', '$p_location_web', '$b_location_web', 
                                 '$title_escaped', 'User Upload', 'User uploaded content', 'User', 0)";

            if ($conn->query($insert_sql)) {

                // Check if user's library table exists
                $check_table = "SHOW TABLES LIKE '$table_name'";
                $table_result = $conn->query($check_table);

                if ($table_result->num_rows == 0) {
                    // Create user library table
                    $create_table_sql = "CREATE TABLE $table_name (
                        bid VARCHAR(3) PRIMARY KEY,
                        pid VARCHAR(3) NOT NULL,
                        p_location VARCHAR(255) NOT NULL,
                        b_location VARCHAR(255) NOT NULL,
                        title VARCHAR(30) NOT NULL,
                        author VARCHAR(20) NOT NULL,
                        description TEXT,
                        genre VARCHAR(10) NOT NULL
                    )";
                    $conn->query($create_table_sql);
                }

                // Add to user's library
                $library_insert = "INSERT INTO $table_name (bid, pid, p_location, b_location, title, author, description, genre) 
                                  VALUES ('$new_bid', '$new_pid', '$p_location_web', '$b_location_web', 
                                         '$title_escaped', 'User Upload', 'User uploaded content', 'User')";
                $conn->query($library_insert);

                $success_message = "Book uploaded successfully!";

                $delete_sql = "DELETE FROM books WHERE bid = '$new_bid'";

                $conn->query($delete_sql);
            } else {
                $errors[] = "Database error: " . $conn->error;
                // Clean up uploaded files if database insert failed
                unlink($pdf_path);
                unlink($cover_path);
            }
        } else {
            $errors[] = "Failed to save uploaded files";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Book - BookNook Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="upload_page.css">
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
                <li><a href="index.php">Home</a></li>
                <li><a href="library.php">Library</a></li>
                <li><a href="upload.php" class="active">User Upload</a></li>

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
        <!-- Welcome Section -->
        <section class="welcome">
            <h2>Upload Your Book</h2>
            <p>Share your own PDF books with a custom cover image.</p>
        </section>

        <!-- Upload Form Section -->
        <section class="upload-section">
            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <p><?php echo $success_message; ?></p>
                    <a href="library.php" class="btn">View in Library</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="upload-form-container">
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="title">
                            <i class="fas fa-book"></i>
                            Book Title *
                        </label>
                        <input type="text" id="title" name="title" required maxlength="30"
                            value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                            placeholder="Enter book title">
                    </div>

                    <div class="form-group">
                        <label for="cover_image">
                            <i class="fas fa-image"></i>
                            Cover Image *
                        </label>
                        <div class="file-input-wrapper">
                            <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
                            <label for="cover_image" class="file-input-label">
                                <i class="fas fa-upload"></i>
                                Choose Cover Image
                            </label>
                            <span class="file-name" id="cover-file-name">No file chosen</span>
                        </div>
                        <small>Supported formats: JPEG, PNG, GIF</small>
                    </div>

                    <div class="form-group">
                        <label for="pdf_file">
                            <i class="fas fa-file-pdf"></i>
                            PDF File *
                        </label>
                        <div class="file-input-wrapper">
                            <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
                            <label for="pdf_file" class="file-input-label">
                                <i class="fas fa-upload"></i>
                                Choose PDF File
                            </label>
                            <span class="file-name" id="pdf-file-name">No file chosen</span>
                        </div>
                        <small>Only PDF files are allowed</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-cloud-upload-alt"></i>
                            Upload Book
                        </button>
                        <a href="library.php" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
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

            // File input handlers
            const coverInput = document.getElementById('cover_image');
            const pdfInput = document.getElementById('pdf_file');
            const coverFileName = document.getElementById('cover-file-name');
            const pdfFileName = document.getElementById('pdf-file-name');

            coverInput.addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
                coverFileName.textContent = fileName;
            });

            pdfInput.addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
                pdfFileName.textContent = fileName;
            });

            // Form validation
            const form = document.querySelector('.upload-form');
            form.addEventListener('submit', function(e) {
                const title = document.getElementById('title').value.trim();
                const coverFile = document.getElementById('cover_image').files[0];
                const pdfFile = document.getElementById('pdf_file').files[0];

                if (!title) {
                    alert('Please enter a book title');
                    e.preventDefault();
                    return;
                }

                if (!coverFile) {
                    alert('Please select a cover image');
                    e.preventDefault();
                    return;
                }

                if (!pdfFile) {
                    alert('Please select a PDF file');
                    e.preventDefault();
                    return;
                }

                // Check file types
                if (!coverFile.type.startsWith('image/')) {
                    alert('Cover must be an image file');
                    e.preventDefault();
                    return;
                }

                if (pdfFile.type !== 'application/pdf') {
                    alert('Please select a valid PDF file');
                    e.preventDefault();
                    return;
                }

                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                submitBtn.disabled = true;
            });
        });
    </script>

</body>

</html>