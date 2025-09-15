<?php
function getCurrentAcademicYear(): string {
    $year = (int)date('Y');
    $nextYear = $year + 1;
    return "{$year}-{$nextYear}";
}
