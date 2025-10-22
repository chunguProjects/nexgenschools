<?php
include('../config/db.php');
include('../model/class.php');

$classModel = new ClassGroup($conn);

$currentClassId = $_GET['current_class_id'] ?? 0;
$targetYearId   = $_GET['target_year_id'] ?? 0;

header('Content-Type: application/json');

if (!$currentClassId || !$targetYearId) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

try {
    // 1. Fetch all existing next-level classes
    $classes = $classModel->getNextLevelClasses((int)$currentClassId, (int)$targetYearId);

    // 2. If none found → auto-create first one
    if (empty($classes)) {
        $autoCreated = $classModel->getOrCreateNextClass((int)$currentClassId, (int)$targetYearId);
        if ($autoCreated) {
            $classes[] = $autoCreated;
        }
    }

    echo json_encode([
        'success' => true,
        'classes' => $classes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'classes' => []
    ]);
}
