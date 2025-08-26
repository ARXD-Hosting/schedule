<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "db.php";


// Login/logout
if (isset($_POST['username'], $_POST['password'])) {
    if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
        $_SESSION['admin'] = true;
    } else {
        $error = "Invalid login";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Redirect to login
if (!isset($_SESSION['admin'])): ?>
<!DOCTYPE html>
<html>
<head>
<title>Schedule Admin Login</title>
<link rel="stylesheet" href="css/styleadmin.css">
<link rel="icon" type="image/png" href="logo.png">
</head>
<body class="login-container">
<div class="login-box">
<h2>Schedule Admin Login</h2>
<form method="post">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</div>
</body>
</html>
<?php exit; endif; ?>

<?php
// Check tables exist
foreach(['staff','shifts'] as $table){
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    if($res->num_rows==0){
        die("<p style='color:red;'>Table '$table' does not exist. Please create it.</p>");
    }
}

// Month/year
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);

// Fetch shifts
$stmt = $conn->prepare("SELECT * FROM shifts WHERE MONTH(shift_date)=? AND YEAR(shift_date)=?");
$stmt->bind_param("ii",$month,$year);
$stmt->execute();
$result = $stmt->get_result();
$shifts = [];
while($row=$result->fetch_assoc()) $shifts[$row['shift_date']][$row['shift_time']]=$row['staff_name'];

// Fetch staff
$staff_pool=[];
$res=$conn->query("SELECT * FROM staff ORDER BY name ASC");
while($row=$res->fetch_assoc()) $staff_pool[]=$row['name'];

// Add/Delete employee
if(isset($_POST['add_employee'])){
    $name=$conn->real_escape_string($_POST['name']);
    if($name) $conn->query("INSERT INTO staff(name) VALUES('$name')");
    header("Location: admin.php"); exit;
}
if(isset($_GET['delete_employee'])){
    $name=$conn->real_escape_string($_GET['delete_employee']);
    $conn->query("DELETE FROM staff WHERE name='$name'");
    header("Location: admin.php"); exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Schedule – ARXD Hosting</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="css/styleadmin.css">
<link rel="icon" type="image/png" href="logo.png">
</head>
<body class="p-4">

<h2>📅 Admin Schedule – <?=date("F Y", strtotime("$year-$month-01"))?></h2>
<a href="?logout=1" class="btn btn-secondary mb-3">Logout</a>

<div class="mb-3">
<a href="?month=<?=($month==1?12:$month-1)?>&year=<?=($month==1?$year-1:$year)?>" class="btn btn-secondary">&laquo; Prev</a>
<a href="?month=<?=($month==12?1:$month+1)?>&year=<?=($month==12?$year+1:$year)?>" class="btn btn-secondary">Next &raquo;</a>
</div>

<div class="container">
<div class="sidebar">
<h4>Staff Pool</h4>
<?php foreach($staff_pool as $s): ?>
<div class="d-flex justify-content-between align-items-center mb-1">
<div class="shift" data-staff="<?=$s?>"><?=$s?></div>
<a href="?delete_employee=<?=urlencode($s)?>" class="btn btn-danger btn-sm">Delete</a>
</div>
<?php endforeach; ?>
<hr>
<h5>Add Employee</h5>
<form method="post">
<input type="text" name="name" placeholder="Employee Name" required>
<button type="submit" name="add_employee" class="btn btn-success btn-sm">Add</button>
</form>

<h5>Actions</h5>
<button id="save-schedule" class="btn btn-primary btn-sm mt-2">💾 Save Schedule</button>
<button id="clear-schedule" class="btn btn-danger btn-sm mt-2">🗑️ Clear Schedule</button>
<button id="undo-clear" class="btn btn-warning btn-sm mt-2">↩️ Undo Last Clear</button>
<button id="send-discord" class="btn btn-info btn-sm mt-2">📤 Send to Discord</button>
<input type="hidden" id="current-month" value="<?= $month ?>">
<input type="hidden" id="current-year" value="<?= $year ?>">
</div>

<div class="calendar">
<?php
$first_day_w = date('N', strtotime("$year-$month-01"));
for ($i=1;$i<$first_day_w;$i++) echo "<div></div>";
for ($d=1;$d<=$days_in_month;$d++){
$date=sprintf("%04d-%02d-%02d",$year,$month,$d);
$morning=$shifts[$date]['Morning']??'';
$afternoon=$shifts[$date]['Afternoon']??'';
echo "<div class='day' data-date='$date'>
<div class='date-num'>$d</div>
<div class='shift-slot droppable' data-shift='Morning'>".($morning?"<div class='shift' data-staff='$morning'>$morning (Morning)</div>":"")."</div>
<div class='shift-slot droppable' data-shift='Afternoon'>".($afternoon?"<div class='shift shift-afternoon' data-staff='$afternoon'>$afternoon (Afternoon)</div>":"")."</div>
</div>";
}
?>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function() {
    let pendingChanges = [];        // Tracks unsaved changes
    let lastClearedShifts = [];     // Tracks cleared shifts for undo

    function makeDraggable() {
        $(".shift").draggable({
            revert: "invalid",
            helper: "clone",
            start: function(e, ui) { $(this).hide(); },
            stop: function(e, ui) { $(this).show(); }
        });
    }
    makeDraggable();

    // Enable dropping shifts onto slots
    $(".droppable").droppable({
        accept: ".shift",
        hoverClass: "droppable-hover",
        drop: function(event, ui) {
            let staff = ui.draggable.data("staff");
            let date  = $(this).closest(".day").data("date");
            let shift = $(this).data("shift");
            let slot  = $(this);

            slot.empty();
            $("<div class='shift "+(shift=="Afternoon"?"shift-afternoon":"")+" shift-unsaved' data-staff='"+staff+"'>"+staff+" ("+shift+")</div>").appendTo(slot);
            makeDraggable();

            // Queue changes
            let existingIndex = pendingChanges.findIndex(c => c.date === date && c.shift === shift);
            if(existingIndex !== -1) {
                pendingChanges[existingIndex].staff = staff;
            } else {
                pendingChanges.push({date: date, shift: shift, staff: staff});
            }
        }
    });

    // Save schedule button
    $("#save-schedule").click(function(){
        if(pendingChanges.length === 0) { 
            alert("No changes to save!"); 
            return; 
        }
        $.post("update_shift.php", {changes: JSON.stringify(pendingChanges)}, function(res){
            alert("Schedule saved!");
            $(".shift-unsaved").removeClass("shift-unsaved");
            pendingChanges = [];
        });
    });

    // Clear schedule button
    $("#clear-schedule").click(function(){
        if(!confirm("Are you sure you want to clear the entire schedule for this month? This cannot be undone.")) return;

        let month = <?=intval($month)?>;
        let year  = <?=intval($year)?>;

        // Save current shifts before clearing
        lastClearedShifts = [];
        $(".day").each(function(){
            let date = $(this).data("date");
            $(this).find(".shift").each(function(){
                let staff = $(this).data("staff");
                let shiftText = $(this).text().includes("Afternoon") ? "Afternoon" : "Morning";
                lastClearedShifts.push({date: date, shift: shiftText, staff: staff});
            });
        });

        $.post("clear_schedule.php", {month: month, year: year}, function(res){
            alert("Schedule cleared!");
            $(".shift").remove(); // visually clear
            pendingChanges = [];
        });
    });

    // Undo last clear button
    $("#undo-clear").click(function(){
        if(lastClearedShifts.length === 0) {
            alert("Nothing to undo!");
            return;
        }

        $.each(lastClearedShifts, function(i, shift){
            let slot = $(".day[data-date='"+shift.date+"'] .shift-slot[data-shift='"+shift.shift+"']");
            slot.empty();
            $("<div class='shift "+(shift.shift=="Afternoon"?"shift-afternoon":"")+" shift-unsaved' data-staff='"+shift.staff+"'>"+shift.staff+" ("+shift.shift+")</div>").appendTo(slot);
        });

        makeDraggable();
        pendingChanges = lastClearedShifts.slice(); // mark all restored shifts as pending
        lastClearedShifts = [];
        alert("Last cleared schedule restored. Don’t forget to save!");
    });

    // Send schedule + pending changes to Discord
    $("#send-discord").click(function() {
        let schedule = {};
        let pending = pendingChanges || [];

        $(".day").each(function() {
            let date = $(this).data("date");
            if (!date) return;
            schedule[date] = [];
            $(this).find(".shift").each(function() {
                let staff = $(this).data("staff");
                if (!staff) return;
                let shiftType = $(this).text().includes("Afternoon") ? "Afternoon" : "Morning";
                schedule[date].push({shift: shiftType, staff: staff});
            });
        });

        $.post("send_to_discord.php", {
            month: $("#current-month").val(),
            year: $("#current-year").val(),
            schedule: JSON.stringify(schedule),
            pending: JSON.stringify(pending)
        }, function(res){
            console.log(res);
            if(res.status==="success") alert("Schedule + pending changes sent to Discord!");
            else alert("Error sending to Discord: "+res.msg);
        }, "json");
    });


});
</script>
</body>
</html>