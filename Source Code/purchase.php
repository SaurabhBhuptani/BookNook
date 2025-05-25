<?php
// Start session to manage user data
session_start();

// Include database connection
include_once "db_connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return error if not logged in
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if the request is POST and has bid
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['bid'])) {
    // Return error if no book ID provided
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Get user ID and book ID
$user_id = $_SESSION['user_id'];
$bid = $_POST['bid'];

// Get book information from the books table
$sql = "SELECT * FROM books WHERE bid = '$bid'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Book not found
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Book not found']);
    exit();
}

$book = $result->fetch_assoc();

// Check if user's library table exists
$table_name = "user_" . $user_id . "_library";
$check_table = "SHOW TABLES LIKE '$table_name'";
$table_result = $conn->query($check_table);

if ($table_result->num_rows == 0) {
    // Table doesn't exist yet, create it
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

    if (!$conn->query($create_table_sql)) {
        // Failed to create table
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error creating library: ' . $conn->error]);
        exit();
    }
}

// Check if book already exists in the user's library
$check_book_sql = "SELECT * FROM $table_name WHERE bid = '$bid'";
$check_result = $conn->query($check_book_sql);

if ($check_result->num_rows > 0) {
    // Book already in library
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You already own this book']);
    exit();
}

// Add book to user's library
// Properly escape values to prevent SQL injection
$title = $conn->real_escape_string($book['title']);
$author = $conn->real_escape_string($book['author']);
$description = $conn->real_escape_string($book['description']);
$genre = $conn->real_escape_string($book['genre']);
$p_location = $conn->real_escape_string($book['p_location']);
$b_location = $conn->real_escape_string($book['b_location']);

$insert_sql = "INSERT INTO $table_name (bid, pid, p_location, b_location, title, author, description, genre) 
               VALUES ('{$book['bid']}', '{$book['pid']}', '$p_location', '$b_location', 
                      '$title', '$author', '$description', '$genre')";

if ($conn->query($insert_sql)) {
    // Success - Return JSON response only, no redirect
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Book added to your library']);
} else {
    // Failed to add book
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error adding book to library: ' . $conn->error]);
}