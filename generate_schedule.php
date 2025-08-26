<?php
// Run this ONCE to populate schedule DB
include "db.php";

$staff = ["Alex W", "Leeland B", "Nathan N", "Noah S", "August M"];
$rotation_offset = 0;

$start_year = 2025;
$start_month = 9;
$months_count = 12;

for ($m = 0; $m < $months_count; $m++) {
    $month = (($start_month + $m - 1) % 12) + 1;
    $year = $start_year + intval(($start_month + $m - 1) / 12);
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    for ($d = 1; $d <= $days_in_month; $d++) {
        $shift_date = sprintf("%04d-%02d-%02d", $year, $month, $d);

        $morning_worker   = $staff[($d + $rotation_offset) % count($staff)];
        $afternoon_worker = $staff[($d + $rotation_offset + 1) % count($staff)];

        $sql = "INSERT INTO shifts (shift_date, shift_time, staff_name) VALUES 
                ('$shift_date','Morning','$morning_worker'),
                ('$shift_date','Afternoon','$afternoon_worker')";
        $conn->query($sql);
    }
    $rotation_offset++;
}

echo "✅ Schedule generated for Sept 2025 – Aug 2026!";
?>