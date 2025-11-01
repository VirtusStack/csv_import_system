<?php
//Features:
// Password-protected access (same as upload password)
// City dropdown to filter data
// Month & Year specify options
// Shows total records count for selected city/month/year
// Download button to export selected data to CSV
// Back + Lock Again (logout) buttons

session_start();
require_once 'config.php';
// Handle CSV download FIRST
if (isset($_SESSION['records_verified']) && $_SESSION['records_verified'] === true &&
    isset($_POST['download_csv']) && !empty($_POST['selected_city'])) {

    $city = $_POST['selected_city'];
    $month = $_POST['selected_month'] ?? '';
    $year = $_POST['selected_year'] ?? '';

    // Build query dynamically
    $query = "SELECT name, contact, city, state, date FROM uploads WHERE city = ?";
    $params = [$city];

    if (!empty($month)) {
        $query .= " AND MONTH(date) = ?";
        $params[] = $month;
    }

    if (!empty($year)) {
        $query .= " AND YEAR(date) = ?";
        $params[] = $year;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($data) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="records_' . preg_replace('/\s+/', '_', $city) . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
           fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
include 'templates/include/header.php';
include 'templates/include/sidebar.php';

// Password Verification
if (isset($_POST['logout'])) {
    unset($_SESSION['records_verified']);
    header("Location: view_records.php");
    exit;
}

$password_verified = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'] ?? '';

    if ($password === UPLOAD_PASSWORD) {
        $_SESSION['records_verified'] = true;
    } else {
        echo "<div class='container-fluid'><div class='alert alert-danger mt-3 text-center'>
                <strong>Error:</strong> Invalid password. Please try again.
              </div></div>";
    }
}
if (isset($_SESSION['records_verified']) && $_SESSION['records_verified'] === true) {
    $password_verified = true;
}

// Fetch City + Year List
$cities = [];
$years = [];

if ($password_verified) {
    $stmt = $pdo->query("SELECT DISTINCT city FROM uploads WHERE city IS NOT NULL AND city != '' ORDER BY city ASC");
    $cities = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->query("SELECT DISTINCT YEAR(date) as year FROM uploads WHERE date IS NOT NULL ORDER BY year DESC");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch Records by Filters
$records = [];
$selected_city = $_POST['selected_city'] ?? '';
$selected_month = $_POST['selected_month'] ?? '';
$selected_year = $_POST['selected_year'] ?? '';
$total_records = 0;

if ($password_verified && !empty($selected_city) && !isset($_POST['download_csv'])) {
    $countQuery = "SELECT COUNT(*) FROM uploads WHERE city = ?";
    $params = [$selected_city];

    if (!empty($selected_month)) {
        $countQuery .= " AND MONTH(date) = ?";
        $params[] = $selected_month;
    }

    if (!empty($selected_year)) {
        $countQuery .= " AND YEAR(date) = ?";
        $params[] = $selected_year;
    }

    // Count total
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $total_records = $countStmt->fetchColumn();

    //  Fetch only first 10 entries for preview
    $query = str_replace("COUNT(*)", "name, contact, city, state, date", $countQuery) . " LIMIT 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!-- MAIN PAGE CONTENT -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800 d-flex justify-content-between align-items-center">
    <span><i class="fas fa-table"></i> View Imported Records</span>
    <div>
      <a href="upload.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
      </a>
      <?php if ($password_verified): ?>
        <form method="post" style="display:inline;">
          <button type="submit" name="logout" class="btn btn-danger btn-sm ml-2">
            <i class="fas fa-lock"></i> Lock Again
          </button>
        </form>
      <?php endif; ?>
    </div>
  </h1>

  <?php if (!$password_verified): ?>
    <!-- Ask Password -->
    <div class="d-flex justify-content-center align-items-center" style="height:70vh;">
      <div class="card shadow p-4" style="width:400px;">
        <h5 class="text-center mb-3">🔒 Enter Password to View Records</h5>
        <form method="post">
          <input type="password" name="password" class="form-control mb-3 text-center" placeholder="Enter Password" required>
          <button type="submit" class="btn btn-primary btn-block">Unlock</button>
        </form>
      </div>
    </div>

  <?php else: ?>
    <!-- Filter + Records -->
    <div class="card shadow mb-4">
      <div class="card-body">
        <form method="post" class="form-inline mb-4">
          <label class="mr-2 font-weight-bold">Select City:</label>
          <select name="selected_city" class="form-control mr-2" required>
            <option value="">-- Choose City --</option>
            <?php foreach ($cities as $city): ?>
              <option value="<?= htmlspecialchars($city) ?>" <?= ($selected_city == $city) ? 'selected' : '' ?>>
                <?= htmlspecialchars($city) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label class="mr-2 font-weight-bold">Month:</label>
          <select name="selected_month" class="form-control mr-2">
            <option value="">All Months</option>
            <?php
              $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
              ];
              foreach ($months as $num => $name):
            ?>
              <option value="<?= $num ?>" <?= ($selected_month == $num) ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
          </select>

          <label class="mr-2 font-weight-bold">Year:</label>
          <select name="selected_year" class="form-control mr-2">
            <option value="">All Years</option>
            <?php foreach ($years as $year): ?>
              <option value="<?= $year ?>" <?= ($selected_year == $year) ? 'selected' : '' ?>><?= $year ?></option>
            <?php endforeach; ?>
          </select>

          <button type="submit" class="btn btn-success mr-2">Show Records</button>

          <?php if (!empty($selected_city)): ?>
            <button type="submit" name="download_csv" value="1" class="btn btn-info">
              <i class="fas fa-download"></i> Download CSV
            </button>
          <?php endif; ?>
        </form>
        <!-- Show total records -->
        <?php if ($total_records > 0): ?>
          <p><strong>Total Records Found:</strong> <?= $total_records ?></p>
        <?php endif; ?>

        <!-- Display Table -->
        <?php if (!empty($records)): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="thead-dark">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Contact</th>
                  <th>City</th>
                  <th>State</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($records as $i => $row): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['contact']) ?></td>
                    <td><?= htmlspecialchars($row['city']) ?></td>
                    <td><?= htmlspecialchars($row['state']) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        <?php elseif ($selected_city): ?>
          <p class="text-danger text-center">No records found for this selection.</p>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include 'templates/include/footer.php'; ?>
