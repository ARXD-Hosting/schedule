<?php
include "db.php";

$result = $conn->query("SELECT shift_date, shift_time, staff_name FROM shifts");
$events = [];

while($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['staff_name'] . " (" . $row['shift_time'] . ")",
        'start' => $row['shift_date'],
        'allDay' => true
    ];
}
echo json_encode($events);
?>