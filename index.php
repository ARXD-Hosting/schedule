<?php
include "db.php";
$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Fetch shifts
$stmt = $conn->prepare("SELECT * FROM shifts WHERE MONTH(shift_date)=? AND YEAR(shift_date)=?");
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();
$shifts = [];
while ($row = $result->fetch_assoc()) {
    $shifts[$row['shift_date']][$row['shift_time']] = $row['staff_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>ARXD Hosting Schedule</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="css/styleadmin.css">
<link rel="icon" type="image/png" href="logo.png">
</head>
<body>
<h2>📅 ARXD Hosting Schedule – <?=date("F Y", strtotime("$year-$month-01"))?></h2>

<div class="mb-3">
    <a href="?month=<?=($month==1?12:$month-1)?>&year=<?=($month==1?$year-1:$year)?>" class="btn btn-secondary">&laquo; Prev</a>
    <a href="?month=<?=($month==12?1:$month+1)?>&year=<?=($month==12?$year+1:$year)?>" class="btn btn-secondary">Next &raquo;</a>
</div>

<div class="calendar">
<?php
$first_day_w = date('N', strtotime("$year-$month-01"));
for ($i = 1; $i < $first_day_w; $i++) echo "<div></div>";

for ($d = 1; $d <= $days_in_month; $d++) {
    $date = sprintf("%04d-%02d-%02d", $year, $month, $d);
    $morning = $shifts[$date]['Morning'] ?? '';
    $afternoon = $shifts[$date]['Afternoon'] ?? '';

    echo "<div class='day'>
            <div class='date-num'>$d</div>"
            .($morning ? "<div class='shift shift-morning'>$morning (Morning)</div>" : "")
            .($afternoon ? "<div class='shift shift-afternoon'>$afternoon (Afternoon)</div>" : "")
         ."</div>";
}
?>
</div>
</body>
</html>