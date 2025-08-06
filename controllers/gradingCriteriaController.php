<?php

session_start();
header('Content-Type: application/json');

include('../config/db.php');
include('../model/GradingCriteriaModel.php');

$schoolId = $_SESSION['user']['school_id'] ?? null;
if (!$schoolId) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$model = new GradingCriteriaModel($conn, $schoolId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = $_POST['level'] ?? '';
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

    // Clean previous criteria for this level
    $model->deleteByLevel($level);

    // Prepare new data
    $criteriaList = [];
    for ($i = 0; $i < count($grades); $i++) {
        $criteriaList[] = [
            'level' => $level,
            'grade' => trim($grades[$i]),
            'min_marks' => (int)$minMarks[$i],
            'max_marks' => (int)$maxMarks[$i],
            'description' => trim($descriptions[$i] ?? ''),
            'form_teacher_remark' => trim($formTeacherRemarks[$i] ?? ''),
            'head_teacher_remark' => trim($headTeacherRemarks[$i] ?? '')
        ];
    }

    $model->insertCriteriaBatch($criteriaList);

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
