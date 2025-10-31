<?php
// Shows: City | Number of Entries
require_once 'config.php';
include 'templates/include/header.php';
include 'templates/include/sidebar.php';

// Fetch city-wise counts
$stmt = $pdo->query("SELECT city, COUNT(*) AS total 
                     FROM uploads 
                     WHERE city IS NOT NULL AND city != '' 
                     GROUP BY city 
                     ORDER BY city ASC");
$cityData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800 d-flex justify-content-between align-items-center">
    <span><i class="fas fa-city"></i> City Wise Data</span>
    <a href="upload.php" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </h1>

  <div class="card shadow mb-4">
    <div class="card-body">
      <?php if (!empty($cityData)): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="thead-dark">
              <tr>
                <th>#</th>
                <th>City</th>
                <th>Number of Entries</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cityData as $i => $row): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= htmlspecialchars($row['city']) ?></td>
                  <td><?= $row['total'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-danger text-center">No records found.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'templates/include/footer.php'; ?>
