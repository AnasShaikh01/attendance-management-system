<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

require_once 'php/node_class.php';

$classes = $_SESSION['classes'] ?? [];
$teacher_id = $_SESSION['teacher_id'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Teacher Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col">

<!-- NAVBAR -->
<nav class="bg-white shadow-sm border-b">
  <div class="container mx-auto px-6 py-4 flex items-center justify-between">
    <h1 class="text-xl font-bold text-blue-600">Online Attendance</h1>
    <div class="flex gap-6 text-sm font-medium text-gray-600">
      <a href="teacher.php" class="text-blue-600">Dashboard</a>
      <a href="student_list.php" class="hover:text-blue-600 transition">Students</a>
      <a href="statistics.php" class="hover:text-blue-600 transition">Statistics</a>
      <a href="profile.php" class="hover:text-blue-600 transition">Profile</a>
      <a href="logout.php" class="text-red-500 hover:text-red-600 transition">Logout</a>
    </div>
  </div>
</nav>

<main class="flex-1 container mx-auto px-6 py-10">

<!-- HEADER -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-12">
  <div>
    <h2 class="text-3xl font-bold text-gray-800">
      Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>
    </h2>
    <p class="text-gray-500 mt-2">
      Manage your classes and track attendance efficiently.
    </p>
  </div>

  <button id="openAddClassModal"
    class="mt-6 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg shadow transition">
    + Add New Class
  </button>
</div>

<!-- CLASS GRID -->
<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">

<?php if (!$classes): ?>

  <div class="col-span-full bg-white rounded-xl shadow p-12 text-center border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-700">No Classes Yet</h3>
    <p class="text-gray-500 mt-2">Create your first class to get started.</p>
  </div>

<?php else: ?>

<?php foreach ($classes as $class_id):

    $node = Node::retrieveObjecti($class_id, $teacher_id);
    if (!$node) continue;

    $code = htmlspecialchars($node->getCode());
    $section = htmlspecialchars($node->getSection());
    $year = htmlspecialchars($node->getYear());
    $semester = htmlspecialchars($node->getSemester());
    $sessions = htmlspecialchars($node->getDays());
?>

<div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-xl transition relative">

  <!-- Top Section -->
  <div class="flex justify-between items-start mb-4">
    <div>
      <h3 class="text-xl font-semibold text-blue-600"><?= $code ?></h3>
      <p class="text-sm text-gray-500 mt-1">
        Section <?= $section ?> • Year <?= $year ?>
      </p>
    </div>

    <div class="flex gap-3">
      <button 
        class="text-gray-400 hover:text-blue-600 text-sm editBtn"
        data-id="<?= $class_id ?>"
        data-code="<?= $code ?>"
        data-year="<?= $year ?>"
        data-section="<?= $section ?>"
        data-semester="<?= $semester ?>">
        Edit
      </button>

      <button 
        class="text-gray-400 hover:text-red-600 text-sm deleteBtn"
        data-id="<?= $class_id ?>">
        Delete
      </button>
    </div>
  </div>

  <!-- Info -->
  <div class="text-sm text-gray-600 space-y-1 mb-6">
    <p><span class="font-medium text-gray-800">Semester:</span> <?= $semester ?></p>
    <p><span class="font-medium text-gray-800">Sessions Conducted:</span> <?= $sessions ?></p>
  </div>

  <!-- Actions -->
  <div class="flex gap-3">
    <a href="take.php?cN=<?= urlencode($class_id) ?>"
       class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm transition">
      Take Attendance
    </a>

    <a href="student_list.php?cN=<?= urlencode($class_id) ?>"
       class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">
      View Students
    </a>
  </div>

</div>

<?php endforeach; ?>
<?php endif; ?>

</div>

</main>

<!-- ADD CLASS MODAL (UNCHANGED LOGIC) -->
<div id="addClassModal"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">

  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8 relative">

    <button id="closeAddClassModal"
      class="absolute top-4 right-5 text-gray-400 hover:text-gray-700 text-2xl">
      &times;
    </button>

    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Add New Class</h2>

    <form id="addClassForm" class="space-y-4">

      <select name="year" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200">
        <?php foreach(range(date('Y'),1983) as $r) echo '<option>'.$r.'</option>'; ?>
      </select>

      <input name="code" type="text" placeholder="Code (e.g. COE-322)"
        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200" required>

      <div class="grid grid-cols-2 gap-4">
        <select name="section" class="border rounded-lg px-3 py-2">
          <?php foreach(range(1,3) as $r) echo '<option>'.$r.'</option>'; ?>
        </select>

        <select name="semester" class="border rounded-lg px-3 py-2">
          <?php foreach(range(1,8) as $r) echo '<option>'.$r.'</option>'; ?>
        </select>
      </div>

      <input name="start" type="text" placeholder="Starting Roll Number"
        class="w-full border rounded-lg px-3 py-2" required>

      <input name="end" type="text" placeholder="Ending Roll Number"
        class="w-full border rounded-lg px-3 py-2" required>

      <div class="flex justify-end gap-3 pt-4">
        <button type="button" id="cancelAddClass"
          class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
          Cancel
        </button>
        <button type="submit"
          class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow">
          Save Class
        </button>
      </div>

    </form>
  </div>
</div>

<script>
// Add Class Modal
const modal = document.getElementById('addClassModal');
const openBtn = document.getElementById('openAddClassModal');
const closeBtn = document.getElementById('closeAddClassModal');
const cancelBtn = document.getElementById('cancelAddClass');

openBtn.onclick = () => modal.classList.replace('hidden','flex');
closeBtn.onclick = cancelBtn.onclick = () => modal.classList.replace('flex','hidden');

modal.onclick = (e) => {
  if (e.target === modal) modal.classList.replace('flex','hidden');
};

// Delete Confirmation
document.querySelectorAll('.deleteBtn').forEach(btn => {
  btn.addEventListener('click', () => {
    if (confirm("Are you sure you want to delete this class?")) {
      window.location.href = "php/delete_class.php?id=" + btn.dataset.id;
    }
  });
});
</script>

</body>
</html>