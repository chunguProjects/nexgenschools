<?php

require_once __DIR__ . '/../model/ResultsCalculator.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    $dbError = 'Database connection not available.';
    $academicYears = $exams = $classes = $subjects = $students = [];
    $selectedYearId = $selectedExamId = $selectedClassId = null;
    $selectedYearName = $selectedExamName = $selectedClassName = '';
    $isJCE = false;
    $metricLabel = 'Pts';
    $summary = ['boys_entered' => 0, 'girls_entered' => 0, 'boys_passed' => 0, 'girls_passed' => 0, 'boys_failed' => 0, 'girls_failed' => 0];
    $totalEntered = $totalPassed = $totalFailed = $passRate = 0;
} else {
    $data = computeResultsSummary($conn, $_GET);
    extract($data);
    // $dbError, $academicYears, $exams, $classes, $subjects, $students,
    // $selectedYearId/Name, $selectedExamId/Name, $selectedClassId/Name,
    // $isJCE, $metricLabel, $summary, $totalEntered, $totalPassed,
    // $totalFailed, $passRate — all set by computeResultsSummary().
}

?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<style>
  .rs-scope{
    --ink:#1B2A3A;
    --paper:#F7F1E3;
    --paper-2:#FBF8F0;
    --green:#1C7A4D;
    --green-dark:#124F32;
    --red:#B0332A;
    --gold:#C7962C;
    --rule:#D9CFB6;
    --muted:#6B6459;
    font-family:'Inter',sans-serif;
    color:var(--ink);
  }
  .rs-scope *{box-sizing:border-box;}
  .rs-sheet{
    max-width:1120px;
    margin:0 auto;
    background:var(--paper);
    border:1px solid var(--rule);
  }
  .rs-masthead{
    background:var(--green-dark);
    color:var(--paper-2);
    padding:22px 32px 20px;
    border-bottom:3px solid var(--gold);
  }
  .rs-masthead-inner{
    display:flex;align-items:center;gap:16px;
    max-width:1056px;margin:0 auto;
  }
  .rs-crest{
    width:46px;height:46px;flex:none;
    border-radius:50%;border:1.5px solid var(--gold);
    display:flex;align-items:center;justify-content:center;
  }
  .rs-crest svg{width:24px;height:24px;stroke:var(--gold);}
  .rs-eyebrow{
    font-size:11px;letter-spacing:.14em;text-transform:uppercase;
    color:#CFE3D6;margin:0 0 2px;font-weight:600;
  }
  .rs-masthead h1{
    font-family:'Fraunces',serif;font-weight:600;font-size:26px;
    margin:0;letter-spacing:.01em;
  }
  .rs-body{padding:28px 32px 40px;max-width:1056px;margin:0 auto;}
  .rs-error{
    background:#F5E1DE;border:1px solid var(--red);color:var(--red);
    padding:14px 18px;font-size:13.5px;margin-bottom:24px;border-radius:2px;
  }
  .rs-empty{
    padding:28px;text-align:center;color:var(--muted);
    border:1px dashed var(--rule);font-size:13.5px;margin-bottom:24px;
  }
  .rs-slip{
    background:var(--paper-2);border:1px dashed #B9AD8C;
    padding:20px 24px 22px;margin-bottom:32px;
  }
  .rs-slip-label{
    font-size:11px;letter-spacing:.12em;text-transform:uppercase;
    color:var(--muted);font-weight:600;margin:0 0 14px;
  }
  .rs-filter-row{
    display:grid;grid-template-columns:1fr 1fr 1fr auto;
    gap:16px;align-items:end;
  }
  .rs-field label{
    display:block;font-size:12px;font-weight:600;
    color:var(--muted);margin-bottom:6px;
  }
  .rs-field select{
    width:100%;height:38px;border:1px solid var(--rule);
    background:#fff;padding:0 10px;font-family:'Inter',sans-serif;
    font-size:14px;color:var(--ink);border-radius:2px;
  }
  .rs-field select:focus{outline:2px solid var(--green);outline-offset:1px;}
  .rs-btn-generate{
    height:38px;padding:0 20px;background:var(--green);color:#fff;
    border:none;font-weight:600;font-size:14px;letter-spacing:.02em;
    cursor:pointer;border-radius:2px;white-space:nowrap;
  }
  .rs-btn-generate:hover{background:var(--green-dark);}
  .rs-report-title{text-align:center;margin-bottom:6px;}
  .rs-report-title .rs-term{
    font-family:'Fraunces',serif;font-weight:600;font-size:22px;margin:0;
  }
  .rs-report-title .rs-sub{
    font-size:13px;color:var(--muted);letter-spacing:.04em;
    text-transform:uppercase;margin:4px 0 0;font-weight:600;
  }
  .rs-divider{width:64px;height:2px;background:var(--gold);margin:14px auto 24px;}
  .rs-actions{display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px;}
  .rs-ghost-btn{
    display:inline-flex;align-items:center;gap:6px;height:32px;padding:0 14px;
    background:transparent;border:1px solid var(--rule);color:var(--ink);
    font-size:12.5px;font-weight:600;cursor:pointer;border-radius:2px;
  }
  .rs-ghost-btn:hover{border-color:var(--ink);}
  .rs-ghost-btn svg{width:14px;height:14px;}
  .rs-table-wrap{
    position:relative;border:1px solid var(--rule);
    overflow-x:auto;margin-bottom:36px;
  }
  table.rs-ledger{width:100%;border-collapse:collapse;font-size:13.5px;min-width:760px;}
  table.rs-ledger thead th{
    background:var(--ink);color:var(--paper-2);font-weight:600;font-size:11.5px;
    letter-spacing:.06em;text-transform:uppercase;padding:10px 12px;
    text-align:left;white-space:nowrap;
  }
  table.rs-ledger thead th.rs-num{text-align:right;}
  table.rs-ledger tbody td{
    padding:10px 12px;border-bottom:1px solid var(--rule);white-space:nowrap;
  }
  table.rs-ledger tbody tr:nth-child(even){background:rgba(199,150,44,0.06);}
  table.rs-ledger tbody tr:hover{background:rgba(28,122,77,0.07);}
  td.rs-mark{text-align:right;}
  .rs-mark-val{
    font-family:'JetBrains Mono',monospace;font-weight:500;
    font-variant-numeric:tabular-nums;
  }
  .rs-mark-grade{
    display:block;font-size:10.5px;color:var(--muted);
    font-family:'JetBrains Mono',monospace;margin-top:1px;
  }
  .rs-mark-dash{color:var(--muted);}
  td.rs-pos{font-family:'JetBrains Mono',monospace;font-weight:600;color:var(--muted);}
  td.rs-pos.rs-top{color:var(--gold);}
  td.rs-name{font-weight:600;}
  td.rs-pts{text-align:right;font-family:'JetBrains Mono',monospace;font-weight:600;}
  .rs-pill{
    display:inline-block;padding:3px 10px;font-size:11px;
    font-weight:700;letter-spacing:.04em;border-radius:2px;
  }
  .rs-pill.rs-pass{background:#E1EFE4;color:#175C34;}
  .rs-pill.rs-fail{background:#F5E1DE;color:var(--red);}
  .rs-pill.rs-pending{background:#EFE9DA;color:var(--muted);}
  .rs-summary-grid{display:grid;grid-template-columns:220px 1fr;gap:28px;align-items:start;}
  .rs-seal-wrap{display:flex;flex-direction:column;align-items:center;padding-top:8px;}
  .rs-seal-caption{
    margin-top:10px;font-size:11px;letter-spacing:.08em;text-transform:uppercase;
    color:var(--muted);font-weight:600;text-align:center;
  }
  .rs-mini-title{
    font-size:11px;letter-spacing:.12em;text-transform:uppercase;
    color:var(--muted);font-weight:600;margin:0 0 12px;
  }
  table.rs-mini{width:100%;border-collapse:collapse;font-size:13.5px;}
  table.rs-mini th{
    text-align:right;font-size:11px;letter-spacing:.06em;text-transform:uppercase;
    color:var(--muted);font-weight:600;padding:0 0 10px 12px;
  }
  table.rs-mini th:first-child{text-align:left;padding-left:0;}
  table.rs-mini td{
    padding:9px 0 9px 12px;border-top:1px solid var(--rule);
    text-align:right;font-family:'JetBrains Mono',monospace;
  }
  table.rs-mini td:first-child{
    text-align:left;font-family:'Inter',sans-serif;font-weight:600;padding-left:0;
  }
  table.rs-mini tr.rs-total td{font-weight:700;}
  table.rs-mini tr.rs-rate td{color:var(--green-dark);font-weight:700;}
  @media (max-width:760px){
    .rs-filter-row{grid-template-columns:1fr;}
    .rs-summary-grid{grid-template-columns:1fr;}
    .rs-body{padding:22px 16px 32px;}
    .rs-masthead{padding:18px 16px;}
  }
  @media print{
    .rs-slip, .rs-actions{display:none !important;}
    body{background:#fff;}
    .rs-sheet{border:none;max-width:none;}
    .rs-masthead{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
  }
</style>

<div class="rs-scope">
<div class="rs-sheet">

  <div class="rs-masthead">
    <div class="rs-masthead-inner">
      <div class="rs-crest">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 4-8 4-8-4 8-4z"/><path d="M4 11v5c0 1 3.6 3 8 3s8-2 8-3v-5"/></svg>
      </div>
      <div>
        <p class="rs-eyebrow">Results summary portal</p>
        <h1><?= htmlspecialchars($schoolName ?? 'Results summary') ?></h1>
      </div>
    </div>
  </div>

  <div class="rs-body">

    <?php if ($dbError): ?>
      <div class="rs-error">Couldn't load results: <?= htmlspecialchars($dbError) ?></div>
    <?php endif; ?>

    <form class="rs-slip" method="get">
      <input type="hidden" name="page" value="summary">
      <p class="rs-slip-label">Generate a report</p>
      <div class="rs-filter-row">
        <div class="rs-field">
          <label for="year">Academic year</label>
          <select id="year" name="year" onchange="this.form.submit()">
            <?php foreach ($academicYears as $year): ?>
              <option value="<?= (int) $year['academic_year_id'] ?>" <?= $year['academic_year_id'] == $selectedYearId ? 'selected' : '' ?>>
                <?= htmlspecialchars($year['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="rs-field">
          <label for="exam">Exam</label>
          <select id="exam" name="exam">
            <?php foreach ($exams as $exam): ?>
              <option value="<?= (int) $exam['id'] ?>" <?= $exam['id'] == $selectedExamId ? 'selected' : '' ?>>
                <?= htmlspecialchars($exam['exam_name'] . ' (' . $exam['term'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="rs-field">
          <label for="class">Class</label>
          <select id="class" name="class">
            <?php foreach ($classes as $class): ?>
              <option value="<?= (int) $class['class_id'] ?>" <?= $class['class_id'] == $selectedClassId ? 'selected' : '' ?>>
                <?= htmlspecialchars($class['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="rs-btn-generate" type="submit">Generate summary</button>
      </div>
    </form>

    <?php if (!$dbError): ?>

      <div class="rs-report-title">
        <p class="rs-term"><?= htmlspecialchars($selectedExamName ?: 'No exam selected') ?> results</p>
        <p class="rs-sub"><?= htmlspecialchars($selectedYearName) ?> &middot; <?= htmlspecialchars($selectedClassName) ?></p>
      </div>
      <div class="rs-divider"></div>

      <div class="rs-actions">
        <a class="rs-ghost-btn" target="_blank" href="summary_print.php?<?= htmlspecialchars(http_build_query([
            'year' => $selectedYearId, 'exam' => $selectedExamId, 'class' => $selectedClassId,
        ])) ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 2v6h6"/></svg>
          Download PDF
        </a>
        <a class="rs-ghost-btn" href="summary_export_excel.php?<?= htmlspecialchars(http_build_query([
            'year' => $selectedYearId, 'exam' => $selectedExamId, 'class' => $selectedClassId,
        ])) ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 2v6h6"/></svg>
          Export Excel
        </a>
      </div>

      <?php if (!$students || !$subjects): ?>
        <div class="rs-empty">No enrolled students or subjects found for this class and academic year yet.</div>
      <?php else: ?>
        <div class="rs-table-wrap">
          <table class="rs-ledger">
            <thead>
              <tr>
                <th>Pos</th>
                <th>Student name</th>
                <?php foreach ($subjects as $subject): ?>
                  <th class="rs-num" title="<?= htmlspecialchars($subject['subject_name']) ?>"><?= htmlspecialchars($subject['subject_code']) ?></th>
                <?php endforeach; ?>
                <th class="rs-num"><?= htmlspecialchars($metricLabel) ?></th>
                <th>Result</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($students as $row): ?>
                <tr>
                  <td class="rs-pos <?= $row['position'] && $row['position'] <= 3 ? 'rs-top' : '' ?>">
                    <?= $row['position'] ?? '—' ?>
                  </td>
                  <td class="rs-name"><?= htmlspecialchars($row['name']) ?></td>
                  <?php foreach ($row['marks'] as $m): ?>
                    <td class="rs-mark">
                      <?php if ($m['marks'] === null): ?>
                        <span class="rs-mark-dash">—</span>
                      <?php else: ?>
                        <span class="rs-mark-val"><?= (int) $m['marks'] ?></span>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                  <td class="rs-pts"><?= $row['has_marks'] ? (int) ($isJCE ? $row['total_marks'] : $row['total_points']) : '—' ?></td>
                  <td>
                    <?php if ($row['result'] === 'Pass'): ?>
                      <span class="rs-pill rs-pass">Pass</span>
                    <?php elseif ($row['result'] === 'Fail'): ?>
                      <span class="rs-pill rs-fail">Fail</span>
                    <?php else: ?>
                      <span class="rs-pill rs-pending">Pending</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="rs-summary-grid">
          <div class="rs-seal-wrap">
            <svg width="150" height="150" viewBox="0 0 150 150">
              <defs>
                <path id="rsRing" d="M 75,75 m -58,0 a 58,58 0 1,1 116,0 a 58,58 0 1,1 -116,0"/>
              </defs>
              <circle cx="75" cy="75" r="70" fill="none" stroke="#1C7A4D" stroke-width="1.5"/>
              <circle cx="75" cy="75" r="58" fill="none" stroke="#C7962C" stroke-width="1.5"/>
              <circle cx="75" cy="75" r="52" fill="#F7F1E3" stroke="#1B2A3A" stroke-width="1"/>
              <text font-family="Fraunces, serif" font-size="30" font-weight="700" fill="#1B2A3A" text-anchor="middle" x="75" y="82"><?= (int) $passRate ?>%</text>
              <text font-family="Inter, sans-serif" font-size="8" font-weight="700" letter-spacing="1" fill="#1C7A4D" text-anchor="middle" x="75" y="100">PASS RATE</text>
              <text font-family="Inter, sans-serif" font-size="7.5" letter-spacing="2.5" fill="#6B6459">
                <textPath href="#rsRing" startOffset="2%">
                  <?= strtoupper(htmlspecialchars($selectedExamName . ' \u2022 ' . $selectedYearName . ' \u2022 ' . $selectedClassName . ' \u2022 ' . $selectedExamName . ' \u2022 ' . $selectedYearName . ' \u2022 ' . $selectedClassName)) ?>
                </textPath>
              </text>
            </svg>
            <p class="rs-seal-caption">Certified class result</p>
          </div>

          <div>
            <p class="rs-mini-title">Enrolment and outcome</p>
            <table class="rs-mini">
              <thead>
                <tr><th></th><th>Boys</th><th>Girls</th><th>Total</th></tr>
              </thead>
              <tbody>
                <tr>
                  <td>Entered</td>
                  <td><?= (int) $summary['boys_entered'] ?></td>
                  <td><?= (int) $summary['girls_entered'] ?></td>
                  <td><?= (int) $totalEntered ?></td>
                </tr>
                <tr>
                  <td>Passed</td>
                  <td><?= (int) $summary['boys_passed'] ?></td>
                  <td><?= (int) $summary['girls_passed'] ?></td>
                  <td><?= (int) $totalPassed ?></td>
                </tr>
                <tr>
                  <td>Failed</td>
                  <td><?= (int) $summary['boys_failed'] ?></td>
                  <td><?= (int) $summary['girls_failed'] ?></td>
                  <td><?= (int) $totalFailed ?></td>
                </tr>
                <tr class="rs-total rs-rate">
                  <td>Pass rate</td>
                  <td colspan="3"><?= (int) $passRate ?>%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</div>
</div>