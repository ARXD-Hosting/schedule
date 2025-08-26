<?php
include "db.php";

$webhook_url = "DISCORD WEBHOOOK URL";

// Get data from POST
$month = intval($_POST['month'] ?? 0);
$year  = intval($_POST['year'] ?? 0);
$schedule_json = $_POST['schedule'] ?? '{}';
$pending_json  = $_POST['pending'] ?? '[]';

if (!$month || !$year) {
    echo json_encode(["status"=>"error","msg"=>"Invalid month or year"]);
    exit;
}

$schedule = json_decode($schedule_json, true);
$pending  = json_decode($pending_json, true);

// Helper: create Discord embed fields
function makeFields($data) {
    $fields = [];
    foreach($data as $date => $shifts) {
        $desc = "";
        foreach($shifts as $shift) {
            $desc .= "{$shift['shift']}: {$shift['staff']}\n";
        }
        $fields[] = [
            "name" => date("M d, Y", strtotime($date)),
            "value" => $desc ?: "_No shifts_",
            "inline" => false
        ];
    }
    if (empty($fields)) {
        $fields[] = ["name"=>"_No shifts_", "value"=>"_Nothing scheduled_", "inline"=>false];
    }
    return $fields;
}

// Helper: split fields into chunks of 25 (Discord limit)
function chunkFields($fields, $size=25) {
    return array_chunk($fields, $size);
}

$embeds = [];

// 1️⃣ Schedule embed(s)
$fields_schedule = makeFields($schedule);
foreach(chunkFields($fields_schedule) as $chunk) {
    $embeds[] = [
        "title" => "changeme" . date("F Y", strtotime("$year-$month-01")),
        "fields" => $chunk,
        "color" => hexdec("45a29e")
    ];
}

// 2️⃣ Pending changes embed(s)
if (!empty($pending)) {
    // Group pending changes by date
    $pending_grouped = [];
    foreach ($pending as $p) {
        $pending_grouped[$p['date']][] = $p;
    }

    $fields_pending = makeFields($pending_grouped);

    foreach(chunkFields($fields_pending) as $chunk) {
        $embeds[] = [
            "title" => "🟡 Pending Changes",
            "fields" => $chunk,
            "color" => hexdec("f1c40f")
        ];
    }
}

// Send webhook
$payload = json_encode(["embeds" => $embeds], JSON_UNESCAPED_UNICODE);

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    "status"=>"success",
    "http_code"=>$http_code,
    "response"=>$response,
    "payload"=>$embeds
]);