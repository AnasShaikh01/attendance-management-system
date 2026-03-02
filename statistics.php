<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

require_once 'php/node_class.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Statistics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col">

<!-- NAVBAR -->
<nav class="bg-white shadow-sm border-b">
  <div class="container mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-blue-600">Online Attendance</h1>
    <div class="flex gap-6 text-sm font-medium text-gray-600">
      <a href="teacher.php" class="hover:text-blue-600 transition">Dashboard</a>
      <a href="student_list.php" class="hover:text-blue-600 transition">Students</a>
      <a href="statistics.php" class="text-blue-600">Statistics</a>
      <a href="profile.php" class="hover:text-blue-600 transition">Profile</a>
      <a href="logout.php" class="text-red-500 hover:text-red-600 transition">Logout</a>
    </div>
  </div>
</nav>

<main class="flex-1 container mx-auto px-6 py-10">

<!-- HEADER -->
<div class="mb-10">
    <h2 class="text-3xl font-bold text-gray-800">
        Attendance Statistics
    </h2>
    <p class="text-gray-500 mt-2">
        Analyze class performance and monitor short attendance.
    </p>
</div>

<?php
$classes = $_SESSION['classes'] ?? [];
$teacher_id = $_SESSION['teacher_id'];

if (!$classes) {
    echo '
    <div class="bg-white rounded-xl shadow p-10 text-center">
        <h3 class="text-lg font-semibold text-gray-700">No Classes Yet</h3>
        <p class="text-gray-500 mt-2">Add classes to view statistics.</p>
    </div>';
} else {

    $data = [];

    foreach ($classes as $c) {

        $node = Node::retrieveObjecti($c, $teacher_id);
        if (!$node) continue;

        $key = $node->getCode().' (Section '.$node->getSection().')';
        $total_days = $node->getDays();

        $data[$key] = [
            "average" => 0,
            "detained" => []
        ];

        if ($total_days > 0) {

            $sum = 0;
            $count = 0;

            foreach ($node->getRecords() as $roll => $rec) {
                $sum += $rec['present'];
                $count++;

                if (($rec['present'] / $total_days) < 0.5) {
                    $data[$key]['detained'][$roll] =
                        round(100 * ($rec['present'] / $total_days), 1);
                }
            }

            $data[$key]['average'] =
                round(($sum / ($count * $total_days)) * 100, 1);
        }
    }

    echo '<script> var statsData = '.json_encode($data).'; </script>';
?>

<!-- SUMMARY CARDS -->
<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 mb-12">

<?php foreach ($data as $class => $d): ?>

<div class="bg-white rounded-xl shadow p-6 border border-gray-100">
    <h3 class="text-lg font-semibold text-blue-600 mb-4">
        <?= htmlspecialchars($class) ?>
    </h3>

    <div class="space-y-2 text-sm text-gray-600">
        <p>
            <span class="font-medium text-gray-800">Average Attendance:</span>
            <span class="text-blue-600 font-semibold"><?= $d['average'] ?>%</span>
        </p>

        <p>
            <span class="font-medium text-gray-800">Short Attendance:</span>
            <span class="text-red-500 font-semibold"><?= count($d['detained']) ?></span>
        </p>
    </div>
</div>

<?php endforeach; ?>

</div>

<!-- CHART SECTION -->
<div class="bg-white rounded-xl shadow p-6 mb-12 border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">
        Average Attendance Comparison
    </h3>
    <div class="relative w-full h-80">
        <canvas id="attendanceChart"></canvas>
    </div>
</div>

<!-- SHORT ATTENDANCE LIST -->
<div class="space-y-8">

<h3 class="text-xl font-semibold text-gray-800">
    Students Below 50% Attendance
</h3>

<?php foreach ($data as $class => $d): ?>

<div class="bg-white rounded-xl shadow p-6 border border-gray-100">

    <div class="flex justify-between items-center mb-5">
        <h4 class="font-semibold text-gray-800">
            <?= htmlspecialchars($class) ?>
        </h4>
        <span class="bg-red-100 text-red-600 text-xs px-3 py-1 rounded-full">
            <?= count($d['detained']) ?> Students
        </span>
    </div>

    <?php if (count($d['detained']) > 0): ?>

        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            <?php foreach ($d['detained'] as $roll => $percent): ?>
                <div class="border border-gray-100 rounded-lg p-4 flex justify-between items-center">
                    <span class="font-medium text-gray-700"><?= $roll ?></span>
                    <span class="text-red-500 font-semibold"><?= $percent ?>%</span>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <p class="text-gray-400 text-sm">
            No students below attendance threshold.
        </p>
    <?php endif; ?>

</div>

<?php endforeach; ?>

</div>

<?php } ?>

</main>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
window.addEventListener("load", function() {

    if (typeof statsData === "undefined") return;

    const labels = Object.keys(statsData);
    const averages = labels.map(c => statsData[c].average);

    const ctx = document.getElementById('attendanceChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Attendance (%)',
                data: averages,
                backgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    min: 0,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

});
</script>

</body>
</html>