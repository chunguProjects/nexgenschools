<?php

session_start();
header('Content-Type: application/json');

include('../config/db.php');
include('../model/GradingCriteriaModel.php');

$model = new GradingCriteriaModel($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = $_POST['level'] ?? '';
    $ids = $_POST['id'] ?? []; // optional IDs for existing rows
    $grades = $_POST['grade'] ?? [];
    $minMarks = $_POST['min_marks'] ?? [];
    $maxMarks = $_POST['max_marks'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $formTeacherRemarks = $_POST['form_teacher_remark'] ?? [];
    $headTeacherRemarks = $_POST['head_teacher_remark'] ?? [];

    if (empty($level) || empty($grades)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required data']);
        exit;
    }

    // Prepare data array
    $criteriaList = [];
    for ($i = 0; $i < count($grades); $i++) {
        $criteriaList[] = [
            'id' => $ids[$i] ?? null, // existing ID if editing
            'level' => $level,
            'grade' => trim($grades[$i]),
            'min_marks' => (int)$minMarks[$i],
            'max_marks' => (int)$maxMarks[$i],
            'description' => trim($descriptions[$i] ?? ''),
            'form_teacher_remark' => trim($formTeacherRemarks[$i] ?? ''),
            'head_teacher_remark' => trim($headTeacherRemarks[$i] ?? '')
        ];
    }

    // Save (update existing or insert new)
    $model->saveCriteriaBatch($criteriaList);

    echo json_encode(['success' => true, 'message' => 'Grading criteria saved successfully']);
    exit;
}

// Handle GET: Return criteria by level
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $level = $_GET['level'] ?? '';
    if (!$level) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing level']);
        exit;
    }

    $criteria = $model->getByLevel($level);
    echo json_encode($criteria);
    exit;
}
