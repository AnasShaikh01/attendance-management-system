<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

require_once 'php/node_class.php';

$teacher_id = $_SESSION['teacher_id'];
$class_id   = $_GET['cN'] ?? null;

if (!$class_id) die("Invalid Request");

$node = Node::retrieveObjecti($class_id, $teacher_id);
if (!$node) die("Class not found.");

$records   = $node->getRecords();
$totalDays = $node->getDays();
$totalStudents = count($records);

// Calculate class average initially
$sum = 0;
foreach ($records as $r) $sum += $r['present'];
$classAverage = ($totalDays > 0)
    ? round(($sum / ($totalStudents * $totalDays)) * 100, 1)
    : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Take Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col pb-24">

<!-- NAVBAR -->
<nav class="bg-white shadow-sm border-b">
  <div class="container mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-blue-600">Online Attendance</h1>
    <div class="flex gap-6 text-sm font-medium text-gray-600">
      <a href="teacher.php" class="hover:text-blue-600">Dashboard</a>
      <a href="statistics.php" class="hover:text-blue-600">Statistics</a>
      <a href="logout.php" class="text-red-500 hover:text-red-600">Logout</a>
    </div>
  </div>
</nav>

<main class="flex-1 container mx-auto px-6 py-10">

<!-- HEADER -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">
        <?= htmlspecialchars($node->getCode()) ?>
    </h2>
    <p class="text-gray-500 mt-2">
        Section <?= htmlspecialchars($node->getSection()) ?> • 
        Year <?= htmlspecialchars($node->getYear()) ?> • 
        Sessions Conducted: <span id="sessionCount"><?= $totalDays ?></span>
    </p>
</div>

<!-- SUMMARY -->
<div class="bg-white rounded-xl shadow p-6 mb-8 flex flex-wrap gap-8">

    <div>
        <p class="text-gray-500 text-sm">Present Today</p>
        <p class="text-2xl font-bold text-green-600" id="presentCount">0</p>
    </div>

    <div>
        <p class="text-gray-500 text-sm">Absent Today</p>
        <p class="text-2xl font-bold text-red-600" id="absentCount"><?= $totalStudents ?></p>
    </div>

    <div>
        <p class="text-gray-500 text-sm">Class Average</p>
        <p class="text-2xl font-bold text-blue-600" id="classAverage">
            <?= $classAverage ?>%
        </p>
    </div>

</div>

<!-- CONTROLS -->
<div class="flex flex-col md:flex-row md:justify-between gap-4 mb-6">

    <div class="flex gap-3">
        <button id="markAll"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
            Mark All Present
        </button>

        <button id="clearAll"
            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg">
            Clear All
        </button>
    </div>

    <input type="text"
           id="searchInput"
           placeholder="Search Roll..."
           class="px-4 py-2 border rounded-lg w-full md:w-64 focus:ring-2 focus:ring-blue-200" />
</div>

<!-- GRID -->
<div id="studentGrid"
     class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">

<?php foreach ($records as $roll => $data): 
    $percent = ($totalDays > 0)
        ? round(($data['present'] / $totalDays) * 100, 1)
        : 0;
?>

<div class="attendance-tile cursor-pointer bg-gray-100 rounded-xl p-4 text-center shadow-sm hover:shadow-md transition"
     data-roll="<?= htmlspecialchars($roll) ?>"
     data-present="0">

    <div class="font-semibold text-gray-800 text-sm">
        <?= htmlspecialchars($roll) ?>
    </div>

    <div class="text-xs text-gray-500 mt-1 overall-percent">
        Overall: <?= $percent ?>%
    </div>

    <div class="hidden total-present">
        <?= $data['present'] ?>
    </div>

    <div class="text-xs mt-2 font-medium status-label text-gray-500">
        Not Marked
    </div>

</div>
<?php endforeach; ?>

</div>

</main>

<!-- SAVE BAR -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-md py-4 px-6 flex justify-between items-center">
    <span class="text-gray-600 font-medium">
        <span id="presentSummary">0</span> students selected
    </span>
    <button id="saveAttendance"
        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
        Save Attendance
    </button>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const tiles = document.querySelectorAll('.attendance-tile');
    const saveBtn = document.getElementById('saveAttendance');
    const markAllBtn = document.getElementById('markAll');
    const clearAllBtn = document.getElementById('clearAll');
    const searchInput = document.getElementById('searchInput');

    const presentCountEl = document.getElementById('presentCount');
    const absentCountEl = document.getElementById('absentCount');
    const summaryEl = document.getElementById('presentSummary');
    const classAverageEl = document.getElementById('classAverage');
    const sessionEl = document.getElementById('sessionCount');

    let presentCount = 0;
    let totalStudents = tiles.length;
    let isSaving = false;

    function updateCounts() {
        presentCountEl.innerText = presentCount;
        absentCountEl.innerText = totalStudents - presentCount;
        summaryEl.innerText = presentCount;
    }

    function toggleTile(tile) {
        if (tile.dataset.present === "0") {
            tile.dataset.present = "1";
            tile.classList.replace('bg-gray-100','bg-green-100');
            tile.querySelector('.status-label').innerText = "Present";
            tile.querySelector('.status-label').classList.replace('text-gray-500','text-green-600');
            presentCount++;
        } else {
            tile.dataset.present = "0";
            tile.classList.replace('bg-green-100','bg-gray-100');
            tile.querySelector('.status-label').innerText = "Not Marked";
            tile.querySelector('.status-label').classList.replace('text-green-600','text-gray-500');
            presentCount--;
        }
        updateCounts();
    }

    tiles.forEach(tile => tile.addEventListener('click', () => toggleTile(tile)));

    markAllBtn.onclick = () => tiles.forEach(t => t.dataset.present==="0" && toggleTile(t));
    clearAllBtn.onclick = () => tiles.forEach(t => t.dataset.present==="1" && toggleTile(t));

    searchInput.addEventListener('input', () => {
        const value = searchInput.value.toLowerCase();
        tiles.forEach(tile => {
            tile.style.display =
                tile.dataset.roll.toLowerCase().includes(value) ? "block" : "none";
        });
    });

    saveBtn.addEventListener('click', async () => {

        if (isSaving) return;
        isSaving = true;
        saveBtn.disabled = true;
        saveBtn.innerText = "Saving...";

        const params = new URLSearchParams();
        params.append('class_id', "<?= $class_id ?>");

        tiles.forEach(tile => {
            if (tile.dataset.present === "1") {
                params.append('present[]', tile.dataset.roll);
            }
        });

        try {

            const response = await fetch('php/save_attendance.php', {
                method: 'POST',
                body: params
            });

            const data = await response.json();

            if (data.success) {

                let newTotalDays = parseInt(sessionEl.innerText) + 1;
                sessionEl.innerText = newTotalDays;

                let sum = 0;

                tiles.forEach(tile => {

                    let totalPresentEl = tile.querySelector('.total-present');
                    let overallEl = tile.querySelector('.overall-percent');
                    let totalPresent = parseInt(totalPresentEl.innerText);

                    if (tile.dataset.present === "1") {
                        totalPresent++;
                        totalPresentEl.innerText = totalPresent;
                    }

                    sum += totalPresent;

                    let newPercent = ((totalPresent / newTotalDays) * 100).toFixed(1);
                    overallEl.innerText = "Overall: " + newPercent + "%";
                });

                let classAvg = ((sum / (tiles.length * newTotalDays)) * 100).toFixed(1);
                classAverageEl.innerText = classAvg + "%";

                saveBtn.innerText = "Saved ✓";
                saveBtn.classList.replace('bg-blue-600','bg-green-600');

                setTimeout(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerText = "Save Attendance";
                    saveBtn.classList.replace('bg-green-600','bg-blue-600');
                    isSaving = false;
                }, 1500);

            } else throw new Error();

        } catch (err) {

            saveBtn.innerText = "Error ❌";
            saveBtn.classList.replace('bg-blue-600','bg-red-600');

            setTimeout(() => {
                saveBtn.disabled = false;
                saveBtn.innerText = "Save Attendance";
                saveBtn.classList.replace('bg-red-600','bg-blue-600');
                isSaving = false;
            }, 2000);
        }

    });

    updateCounts();
});
</script>

</body>
</html>