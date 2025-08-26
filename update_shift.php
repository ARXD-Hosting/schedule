<?php
include "db.php";

if(isset($_POST['changes'])){
$changes=json_decode($_POST['changes'],true);
foreach($changes as $c){
$date=$conn->real_escape_string($c['date']);
$shift=$conn->real_escape_string($c['shift']);
$staff=$conn->real_escape_string($c['staff']);

$res=$conn->query("SELECT id FROM shifts WHERE shift_date='$date' AND shift_time='$shift'");
if($res->num_rows>0){
$conn->query("UPDATE shifts SET staff_name='$staff' WHERE shift_date='$date' AND shift_time='$shift'");
}else{
$conn->query("INSERT INTO shifts (shift_date,shift_time,staff_name) VALUES ('$date','$shift','$staff')");
}
}
echo json_encode(["status"=>"success","saved"=>count($changes)]);
}else{
echo json_encode(["status"=>"error","msg"=>"No changes received"]);
}