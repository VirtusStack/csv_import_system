<?php
// Displays "Previous Records" and "New Record Add" buttons
//  User clicks "New Record Add" → goes to upload_form.php

include 'templates/include/header.php';
include 'templates/include/sidebar.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Page Heading -->
  <h1 class="h3 mb-4 text-gray-800">
    <i class="fas fa-database"></i> CSV Upload Management
  </h1>

  <!-- Buttons Card -->
  <div class="card shadow mb-4">
    <div class="card-body text-center">

      <!-- Previous Records Button -->
      <a href="view_records.php" class="btn btn-secondary btn-lg mx-2">
        <i class="fas fa-history"></i> Previous Records
      </a>

      <!-- New Record Add Button -->
      <a href="upload_form.php" class="btn btn-primary btn-lg mx-2">
        <i class="fas fa-plus-circle"></i> New Record Add
      </a>

    </div>
  </div>

</div>
<!-- End of Container -->

<?php include 'templates/include/footer.php'; ?>

