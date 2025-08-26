<?php
include "db.php";

$month = intval($_POST['month'] ?? 0);
$year  = intval($_POST['year'] ?? 0);

if($month && $year){
    $stmt = $conn->prepare("DELETE FROM shifts WHERE MONTH(shift_date)=? AND YEAR(shift_date)=?");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error","msg"=>"Invalid month or year"]);
}