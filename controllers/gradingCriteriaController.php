<?php

session_start();
header('Content-Type: application/json');

include('../config/db.php');
include('../model/GradingCriteriaModel.php');

$model = new GradingCriteriaModel($conn);

/* =========================
   POST: SAVE / UPDATE / DELETE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $level = $_POST['level'] ?? '';

    $ids = $_POST['id'] ?? [];
    $grades = $_POST['grade'] ?? [];
    $minMarks = $_POST['min_marks'] ?? [];
    $maxMarks = $_POST['max_marks'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $formTeacherRemarks = $_POST['form_teacher_remark'] ?? [];
    $headTeacherRemarks = $_POST['head_teacher_remark'] ?? [];

    // 🔥 NEW: deleted rows from UI
    $deletedIds = $_POST['deleted_ids'] ?? [];

    if (empty($level) || empty($grades)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required data']);
        exit;
    }

    /* =========================
       STEP 1: DELETE REMOVED ROWS
    ========================= */
    if (!empty($deletedIds)) {
        foreach ($deletedIds as $delId) {
            if (!empty($delId) && is_numeric($delId)) {
                $stmt = $conn->prepare("DELETE FROM grading_criteria WHERE id = ?");
                $stmt->bind_param("i", $delId);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    /* =========================
       STEP 2: PREPARE DATA
    ========================= */
    $criteriaList = [];

    for ($i = 0; $i < count($grades); $i++) {

        $grade = trim($grades[$i] ?? '');

        if ($grade === '') continue;

        $criteriaList[] = [
            'id' => (!empty($ids[$i]) && is_numeric($ids[$i])) ? (int)$ids[$i] : null,
            'level' => $level,
            'grade' => $grade,
            'min_marks' => (int)($minMarks[$i] ?? 0),
            'max_marks' => (int)($maxMarks[$i] ?? 0),
            'description' => trim($descriptions[$i] ?? ''),
            'form_teacher_remark' => trim($formTeacherRemarks[$i] ?? ''),
            'head_teacher_remark' => trim($headTeacherRemarks[$i] ?? '')
        ];
    }

    if (empty($criteriaList)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid rows to save']);
        exit;
    }

    /* =========================
       STEP 3: SAVE (INSERT / UPDATE)
    ========================= */
    try {
        $model->saveCriteriaBatch($criteriaList);

        echo json_encode([
            'success' => true,
            'message' => 'Grading criteria saved successfully'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }

    exit;
}

/* =========================
   GET: LOAD BY LEVEL
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $level = $_GET['level'] ?? '';

    if (empty($level)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing level']);
        exit;
    }

    $criteria = $model->getByLevel($level);

    echo json_encode($criteria);
    exit;
}