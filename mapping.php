<?php
// STEP 3 - CSV HEADER MAPPING PAGE
// 1. Checks password
// 2. Validates and uploads CSV file
// 3. Reads first row to detect headers
// 4. Displays a dropdown mapping form
// 5. On submit → goes to import.php

include 'templates/include/header.php';
include 'templates/include/sidebar.php';
require_once 'config.php';

// Validate password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password !== UPLOAD_PASSWORD) {
        echo "<div class='container-fluid'><div class='alert alert-danger mt-3'>
                <strong>Error:</strong> Invalid password. Please try again.
              </div></div>";
        include 'templates/include/footer.php';
        exit;
    }

    // Validate file
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo "<div class='container-fluid'><div class='alert alert-danger mt-3'>
                <strong>Error:</strong> Please upload a valid CSV file.
              </div></div>";
        include 'templates/include/footer.php';
        exit;
    }

    // Save uploaded file temporarily
    $tmpName = $_FILES['csv_file']['tmp_name'];
    $fileName = basename($_FILES['csv_file']['name']);
    $targetPath = UPLOAD_PATH . "/" . time() . "_" . $fileName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        echo "<div class='container-fluid'><div class='alert alert-danger mt-3'>
                <strong>Error:</strong> Failed to move uploaded file.
              </div></div>";
        include 'templates/include/footer.php';
        exit;
    }

    // Read first line for headers
    $file = fopen($targetPath, 'r');
    $headers = fgetcsv($file);

    // Detect if header line looks invalid or has too few columns
    if (!$headers || count($headers) < 2) {
        rewind($file);
        $firstRow = fgetcsv($file);
        $colCount = count($firstRow);
        $headers = [];

        // Generate fake headers: Column 1, Column 2, ...
        for ($i = 1; $i <= $colCount; $i++) {
            $headers[] = "Column $i";
        }

        rewind($file);
    }

    fclose($file);
} else {
    // If accessed directly without form submission
    header("Location: upload_form.php");
    exit;
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-list"></i> Step 3: Map Your CSV Columns</h1>

  <!-- Mapping Card -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Column Mapping</h6>
      <a href="upload_form.php" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
      </a>
    </div>

    <div class="card-body">
      <form action="import.php" method="POST">

        <!-- Hidden field to send uploaded CSV path -->
        <input type="hidden" name="csv_path" value="<?= htmlspecialchars($targetPath) ?>">

        <p class="text-muted">Please map your CSV headers to the standard system fields.</p>

        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="thead-light">
              <tr>
                <th>System Field</th>
                <th>Select CSV Header</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Create dropdown mapping rows
              $systemFields = ['name', 'contact', 'city', 'state', 'date'];

              foreach ($systemFields as $field):
                  $isRequired = in_array($field, ['name', 'contact']) ? 'required' : '';
              ?>
              <tr>
                <td><strong><?= ucfirst($field) ?></strong></td>
                <td>
                  <?php if ($field === 'date'): ?>
                    <!-- ✅ Date field: select header OR manually choose date -->
                    <div class="input-group">
                      <select name="mapping[<?= $field ?>]" class="form-control">
                        <option value="">-- Select Header --</option>
                        <?php foreach ($headers as $h): ?>
                          <option value="<?= htmlspecialchars($h) ?>"><?= htmlspecialchars($h) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <span class="input-group-text">or</span>
                      <input type="date" name="manual_date" class="form-control" placeholder="Select date manually">
                    </div>
                    <small class="text-muted">(Optional — select CSV column or enter date manually)</small>
                  <?php else: ?>
                    <select name="mapping[<?= $field ?>]" class="form-control" <?= $isRequired ?>>
                      <option value="">-- Select Header --</option>
                      <?php foreach ($headers as $h): ?>
                        <option value="<?= htmlspecialchars($h) ?>"><?= htmlspecialchars($h) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
          <button type="submit" name="import" class="btn btn-success btn-lg">
            <i class="fas fa-database"></i> Import to MySQL
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include 'templates/include/footer.php'; ?>
