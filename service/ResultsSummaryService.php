
<?php

require_once('../utils/grades-utils.php');


class ResultsSummaryService {


private $conn;


public function __construct($conn)
{
    $this->conn=$conn;
}




public function getSummary($classId,$examId)
{


// students

$stmt=$this->conn->prepare("
SELECT *
FROM students
WHERE class_id=?
AND is_deleted=0
");


$stmt->bind_param("i",$classId);

$stmt->execute();

$students=$stmt->get_result()
->fetch_all(MYSQLI_ASSOC);





// subjects

$stmt=$this->conn->prepare("
SELECT s.*
FROM subjects s

JOIN class_subject cs
ON cs.subject_id=s.subject_id

WHERE cs.class_id=?
");


$stmt->bind_param("i",$classId);

$stmt->execute();


$subjects=$stmt->get_result()
->fetch_all(MYSQLI_ASSOC);





// grades

$stmt=$this->conn->prepare("
SELECT *
FROM grades
WHERE class_id=?
AND exam_id=?
");


$stmt->bind_param("ii",$classId,$examId);

$stmt->execute();


$grades=$stmt->get_result()
->fetch_all(MYSQLI_ASSOC);






// organize grades


$studentMarks=[];


foreach($grades as $grade)
{

$studentMarks
[$grade['student_id']]
[$grade['subject_id']]
=
$grade['marks'];

}






return [

'students'=>$students,

'subjects'=>$subjects,

'marks'=>$studentMarks

];


}




}