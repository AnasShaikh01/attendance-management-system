<?php
require_once 'php/node_class.php';

$year    = intval($_POST['year'] ?? 0);
$section = intval($_POST['section'] ?? 0);
$code    = strtoupper(trim($_POST['code'] ?? ''));
$roll    = strtoupper(trim($_POST['roll'] ?? ''));

if (!$year || !$section || !$code || !$roll) {
    die("Invalid request.");
}

$con = connectTo();

$stmt = $con->prepare(
    "SELECT object FROM objects
     WHERE code = ? AND section = ? AND year = ?"
);

$stmt->bind_param("sii", $code, $section, $year);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Class not found.");
}

$row = $result->fetch_assoc();
$node = unserialize($row['object'], ['allowed_classes' => ['Node']]);

$stmt->close();
$con->close();

$records = $node->getRecords();

if (!isset($records[$roll])) {
    die("Roll number not found.");
}

$total   = $node->getDays();
$present = $records[$roll]['present'];
$percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;

$statusColor = "bg-green-500";
$statusText  = "Safe Attendance";

if ($percent < 75 && $percent >= 50) {
    $statusColor = "bg-yellow-500";
    $statusText  = "Attendance Warning";
}

if ($percent < 50) {
    $statusColor = "bg-red-500";
    $statusText  = "Critical – Short Attendance";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Attendance Result</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col">

<nav class="bg-white border-b shadow-sm">
  <div class="container mx-auto px-6 py-4 flex justify-between">
    <a href="student.php" class="text-blue-600 font-semibold">← Back</a>
    <span class="text-gray-600 text-sm">Student Attendance Result</span>
  </div>
</nav>

<main class="flex-1 container mx-auto px-6 py-16">

<div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-12 max-w-5xl mx-auto">

<div class="flex flex-col md:flex-row items-center justify-between gap-16">

<div class="flex-1 space-y-6">
  <h2 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($code) ?></h2>

  <div class="space-y-2 text-gray-600 text-lg">
    <p><strong>Total Classes:</strong> <?= $total ?></p>
    <p><strong>Classes Attended:</strong> <?= $present ?></p>
  </div>

  <div class="inline-block px-5 py-2 text-white rounded-full <?= $statusColor ?>">
    <?= $statusText ?>
  </div>
</div>

<div class="flex flex-col items-center">
  <div class="relative w-44 h-44">
    <svg class="w-full h-full transform -rotate-90">
      <circle cx="88" cy="88" r="75"
        stroke="#e5e7eb"
        stroke-width="14"
        fill="transparent" />
      <circle cx="88" cy="88" r="75"
        stroke="#2563EB"
        stroke-width="14"
        fill="transparent"
        stroke-dasharray="<?= 2 * M_PI * 75 ?>"
        stroke-dashoffset="<?= 2 * M_PI * 75 * (1 - $percent / 100) ?>"
        stroke-linecap="round" />
    </svg>
    <div class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-gray-800">
      <?= $percent ?>%
    </div>
  </div>
  <p class="text-gray-500 mt-4">Overall Attendance</p>
</div>

</div>

<div class="mt-12">
  <div class="w-full bg-gray-200 rounded-full h-4">
    <div class="h-4 rounded-full bg-blue-600"
         style="width:<?= $percent ?>%">
    </div>
  </div>
</div>

</div>

</main>

</body>
</html>