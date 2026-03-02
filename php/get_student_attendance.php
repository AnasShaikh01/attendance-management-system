<?php
header('Content-Type: application/json');
require_once 'node_class.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

$year    = intval($_POST['year'] ?? 0);
$section = intval($_POST['section'] ?? 0);
$code    = strtoupper(trim($_POST['code'] ?? ''));
$roll    = strtoupper(trim($_POST['roll'] ?? ''));

if (!$year || !$section || !$code || !$roll) {
    echo json_encode(["error" => "All fields are required."]);
    exit();
}

$con = connectTo();

/* 🔥 Find matching class */
$stmt = $con->prepare(
    "SELECT uid, object FROM objects
     WHERE code = ? AND section = ? AND year = ?"
);

$stmt->bind_param("sii", $code, $section, $year);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Class not found."]);
    exit();
}

$row = $result->fetch_assoc();
$node = unserialize($row['object'], ['allowed_classes' => ['Node']]);

$stmt->close();
$con->close();

$records = $node->getRecords();

if (!isset($records[$roll])) {
    echo json_encode(["error" => "Roll number not found."]);
    exit();
}

$total   = $node->getDays();
$present = $records[$roll]['present'];
$percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;

echo json_encode([
    "success" => true,
    "code"    => $code,
    "total"   => $total,
    "present" => $present,
    "percent" => $percent
]);
exit();
?>