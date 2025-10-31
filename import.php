<?php
// STEP 4 - IMPORT CSV TO MYSQL (uploads table)
// 1. Receives mapped headers + CSV path
// 2. Reads file, applies mapping
// 3. Inserts into `uploads` table
// 4. Moves file to /uploads/processed/
// 5. Displays success message

include 'templates/include/header.php';
include 'templates/include/sidebar.php';
require_once 'config.php';

// Validate submission
if (!isset($_POST['import']) || !isset($_POST['csv_path'])) {
    echo "<div class='container-fluid'><div class='alert alert-danger mt-3'>Invalid request.</div></div>";
    include 'templates/include/footer.php';
    exit;
}

$csvPath = $_POST['csv_path'];
$mapping = $_POST['mapping'] ?? [];
$manualDate = $_POST['manual_date'] ?? '';  

if (!file_exists($csvPath)) {
    echo "<div class='container-fluid'><div class='alert alert-danger mt-3'>File not found!</div></div>";
    include 'templates/include/footer.php';
    exit;
}

// Read CSV and skip header row
$file = fopen($csvPath, 'r');
$headers = fgetcsv($file); // skip header line

$rowCount = 0;
$inserted = 0;

// Prepare SQL for `uploads` table
$stmt = $pdo->prepare("INSERT INTO uploads (name, contact, city, state, date, uploaded_file) VALUES (?, ?, ?, ?, ?, ?)");

while (($row = fgetcsv($file)) !== false) {
    $data = [];

    // Mapping columns dynamically
    foreach (['name', 'contact', 'city', 'state', 'date'] as $field) {
        $col = $mapping[$field] ?? '';
        $index = array_search($col, $headers);
        $data[$field] = ($index !== false && isset($row[$index])) ? trim($row[$index]) : null;
    }

    // ✅ If CSV date not provided, use manual date if available
    $finalDate = null;
    if (!empty($data['date'])) {
        $finalDate = date('Y-m-d', strtotime($data['date']));
    } elseif (!empty($manualDate)) {
        $finalDate = $manualDate;
    }

    // Insert into DB
    try {
        $stmt->execute([
            $data['name'],
            $data['contact'],
            $data['city'],
            $data['state'],
            $finalDate,
            basename($csvPath)
        ]);
        $inserted++;
    } catch (Exception $e) {
        error_log("Insert failed: " . $e->getMessage());
    }

    $rowCount++;
}

fclose($file);

// Move file to processed folder
$newPath = PROCESSED_PATH . "/" . basename($csvPath);
rename($csvPath, $newPath);

// Show success message
?>
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-check-circle text-success"></i> Import Complete</h1>

  <div class="card shadow mb-4">
    <div class="card-body text-center">
      <h5 class="text-success">✅ Your file has been successfully imported!</h5>
      <p><strong>File:</strong> <?= htmlspecialchars(basename($csvPath)) ?></p>
      <p><strong>Total Rows Read:</strong> <?= $rowCount ?></p>
      <p><strong>Rows Imported:</strong> <?= $inserted ?></p>

      <a href="view_records.php" class="btn btn-primary mt-3">
        <i class="fas fa-table"></i> View Records
      </a>
    </div>
  </div>
</div>

<?php include 'templates/include/footer.php'; ?>

