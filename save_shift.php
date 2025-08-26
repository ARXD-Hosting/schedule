<?php
include "db.php";
$data = json_decode(file_get_contents("php://input"), true);

$date = $conn->real_escape_string($data['date']);
$shift = $conn->real_escape_string($data['shift']);
$staff = $conn->real_escape_string($data['staff']);

// Check if shift already exists for that date/shift_time
$check = $conn->query("SELECT id FROM shifts WHERE shift_date='$date' AND shift_time='$shift'");
if($check->num_rows > 0) {
    $conn->query("UPDATE shifts SET staff_name='$staff' WHERE shift_date='$date' AND shift_time='$shift'");
} else {
    $conn->query("INSERT INTO shifts (shift_date, shift_time, staff_name) VALUES ('$date','$shift','$staff')");
}
echo "success";
?>