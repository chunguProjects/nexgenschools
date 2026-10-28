<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header("Location: ../views/login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';           // gives us $conn
require_once __DIR__ . '/../model/ResultsCalculator.php';
require_once __DIR__ . '/../model/GeneralSettingsModel.php';

$settingsModel = new GeneralSettingsModel($conn);
$settings = $settingsModel->getSettings();
$schoolName = $settings['school_name'] ?? 'NextGen Schools';

$data = computeResultsSummary($conn, $_GET);
extract($data); // dbError, subjects, students, selected*Name, isJCE, metricLabel, summary, totals, passRate
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($schoolName) ?> — Results Summary</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<style>
  @media print{
    @page{ size: A4 landscape; margin: 0; } /* removes the browser's own header/footer strip */
    html, body{ margin:0; }
    .print-sheet{ padding: 10mm 12mm; }
    .table-responsive{ overflow: visible; }
    table{ min-width: 0; }
    table tbody td{ white-space: normal; }
    table thead{ display: table-header-group; } /* repeat header on every printed page */
    table tbody tr{ page-break-inside: avoid; break-inside: avoid; }
    .summary-block{ page-break-inside: avoid; break-inside: avoid; }
  }
</style>
</head>
<body>

<div class="container-fluid print-sheet">

  <?php if ($dbError): ?>
    <div class="alert alert-danger">Couldn't load results: <?= htmlspecialchars($dbError) ?></div>
  <?php else: ?>

    <div class="card shadow-sm mb-3">
      <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-1"><?= htmlspecialchars($schoolName) ?></h4>
        <div><?= htmlspecialchars($selectedExamName ?: 'No exam selected') ?> Results</div>
        <small><?= htmlspecialchars($selectedYearName) ?> &middot; <?= htmlspecialchars($selectedClassName) ?></small>
      </div>
    </div>

    <?php if (!$students || !$subjects): ?>
      <div class="alert alert-warning">No enrolled students or subjects found for this class and academic year yet.</div>
    <?php else: ?>

      <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm align-middle">
          <thead class="table-primary">
            <tr>
              <th>POS</th>
              <th>STUDENT NAME</th>
              <?php foreach ($subjects as $subject): ?>
                <th><?= htmlspecialchars($subject['subject_code']) ?></th>
              <?php endforeach; ?>
              <th><?= htmlspecialchars($metricLabel) ?></th>
              <th>Res</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $row): ?>
              <tr>
                <td><?= $row['position'] ?? '—' ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <?php foreach ($row['marks'] as $m): ?>
                  <td><?= $m['marks'] === null ? '—' : (int) $m['marks'] ?></td>
                <?php endforeach; ?>
                <td><?= $row['has_marks'] ? (int) ($isJCE ? $row['total_marks'] : $row['total_points']) : '—' ?></td>
                <td>
                  <?php if ($row['result'] === 'Pass'): ?>
                    <span class="badge bg-success">Pass</span>
                  <?php elseif ($row['result'] === 'Fail'): ?>
                    <span class="badge bg-danger">Fail</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Pending</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="summary-block">
        <h5 class="text-center">SUMMARY</h5>
        <table class="table table-bordered text-center">
          <thead class="table-secondary">
            <tr><th></th><th>BOYS</th><th>GIRLS</th><th>TOTAL</th></tr>
          </thead>
          <tbody>
            <tr>
              <td>ENTERED</td>
              <td><?= (int) $summary['boys_entered'] ?></td>
              <td><?= (int) $summary['girls_entered'] ?></td>
              <td><?= (int) $totalEntered ?></td>
            </tr>
            <tr>
              <td>PASSED</td>
              <td><?= (int) $summary['boys_passed'] ?></td>
              <td><?= (int) $summary['girls_passed'] ?></td>
              <td><?= (int) $totalPassed ?></td>
            </tr>
            <tr>
              <td>FAILED</td>
              <td><?= (int) $summary['boys_failed'] ?></td>
              <td><?= (int) $summary['girls_failed'] ?></td>
              <td><?= (int) $totalFailed ?></td>
            </tr>
            <tr>
              <td>PASS %</td>
              <td colspan="3"><?= (int) $passRate ?>%</td>
            </tr>
          </tbody>
        </table>
      </div>

    <?php endif; ?>
  <?php endif; ?>

</div>

<script>
  window.addEventListener('load', function () {
    window.print();
  });
</script>

</body>
</html>