<?php
// Show errors 
ini_set("display_errors", true);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// ---------------------
// Database Configuration
// ---------------------
define("DB_HOST", "localhost");
define("DB_NAME", "csv_import_system");
define("DB_USER", "root");
define("DB_PASS", "");

// DSN for PDO
define("DB_DSN", "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME);

// PDO connection
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Project paths
define("BASE_URL", "http://localhost/csv_import_system");

// Upload folders (absolute paths)
define("UPLOAD_PATH", __DIR__ . "/uploads");

// Create folders if missing
if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0777, true);

// System Configuration

// Password for uploading CSV (you can change anytime)
define("UPLOAD_PASSWORD", "admin123");

// Max number of rows allowed per file
define("MAX_ROWS_LIMIT", 10000);

// Global Exception Handler
function handleException($exception) {
    echo "<pre><strong>ERROR:</strong> " . $exception->getMessage() . "</pre>";
    error_log($exception->getMessage());
}
set_exception_handler('handleException');
?>
