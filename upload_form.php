<?php
// UPLOAD FORM PAGE 
// - User arrives here after clicking “New Record Add”
// - Form has: CSV upload + password field
// - Validates upload limit (MAX_ROWS_LIMIT)
// - On submit → goes to mapping.php (next step)

include 'templates/include/header.php';
include 'templates/include/sidebar.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Page Title -->
  <h1 class="h3 mb-4 text-gray-800">
    <i class="fas fa-upload"></i> Upload New CSV File
  </h1>

  <!-- Card: Upload Form -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Step 2: Upload File & Authenticate</h6>
      <a href="upload.php" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
      </a>
    </div>

    <div class="card-body">
      <!-- Upload Form -->
      <form action="mapping.php" method="POST" enctype="multipart/form-data">

        <!-- CSV File -->
        <div class="form-group">
          <label for="csv_file"><strong>Select CSV File:</strong></label>
          <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
          <small class="text-muted">Only .csv files allowed. Max <?= MAX_ROWS_LIMIT ?> rows per upload.</small>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password"><strong>Upload Password:</strong></label>
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter authorized password" required>
          <small class="text-muted">Only authorized users with password can upload.</small>
        </div>

        <!-- Submit -->
        <div class="text-center">
          <button type="submit" name="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-arrow-right"></i> Continue to Mapping
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
<!-- End Page Content -->

<?php include 'templates/include/footer.php'; ?>
