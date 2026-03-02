<?php
session_start();
require_once 'php/defines.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: index.php');
    exit();
}

$conn = connectTo();

// Search functionality
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $conn->prepare("SELECT id, name, roll_number, class, section 
                            FROM students 
                            WHERE name LIKE CONCAT('%', ?, '%') 
                               OR roll_number LIKE CONCAT('%', ?, '%')
                            ORDER BY name ASC");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT id, name, roll_number, class, section 
                            FROM students 
                            ORDER BY name ASC");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Students</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col">

<!-- NAVBAR -->
<nav class="bg-white shadow-sm border-b">
  <div class="container mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-blue-600">Online Attendance</h1>
    <div class="flex gap-6 text-sm font-medium text-gray-600">
      <a href="teacher.php" class="hover:text-blue-600">Dashboard</a>
      <a href="student_list.php" class="text-blue-600">Students</a>
      <a href="statistics.php" class="hover:text-blue-600">Statistics</a>
      <a href="profile.php" class="hover:text-blue-600">Profile</a>
      <a href="logout.php" class="text-red-500 hover:text-red-600">Logout</a>
    </div>
  </div>
</nav>

<main class="flex-1 container mx-auto px-6 py-10">

  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
    <div>
      <h2 class="text-3xl font-bold text-gray-800">Student Management</h2>
      <p class="text-gray-500 mt-1">View, search and manage students easily.</p>
    </div>

    <button id="openModal"
      class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow transition">
      + Add Student
    </button>
  </div>

  <!-- SEARCH BAR -->
  <form method="GET" class="mb-6">
    <div class="relative">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
        placeholder="Search by name or roll number..."
        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-200">
      <button type="submit"
        class="absolute right-3 top-2.5 text-sm text-blue-600 font-medium">
        Search
      </button>
    </div>
  </form>

  <!-- TABLE -->
  <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">

    <?php if ($result && $result->num_rows > 0): ?>

      <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-6 py-4">Name</th>
            <th class="px-6 py-4">Roll Number</th>
            <th class="px-6 py-4">Class</th>
            <th class="px-6 py-4">Section</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">

          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="px-6 py-4 font-medium text-gray-800">
                <?= htmlspecialchars($row['name']) ?>
              </td>
              <td class="px-6 py-4 text-gray-600">
                <?= htmlspecialchars($row['roll_number']) ?>
              </td>
              <td class="px-6 py-4 text-gray-600">
                <?= htmlspecialchars($row['class']) ?>
              </td>
              <td class="px-6 py-4 text-gray-600">
                <?= htmlspecialchars($row['section']) ?>
              </td>
            </tr>
          <?php endwhile; ?>

        </tbody>
      </table>

    <?php else: ?>

      <div class="p-12 text-center">
        <h3 class="text-lg font-semibold text-gray-700">No Students Found</h3>
        <p class="text-gray-500 mt-2">Try adding students or adjusting search filters.</p>
      </div>

    <?php endif; ?>

  </div>

</main>

<!-- ADD STUDENT MODAL -->
<div id="studentModal"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">

  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 relative">

    <button id="closeModal"
      class="absolute top-4 right-5 text-gray-400 hover:text-gray-700 text-2xl">
      &times;
    </button>

    <h2 class="text-xl font-semibold mb-6 text-gray-800">Add Student</h2>

    <form action="add_student.php" method="POST" class="space-y-4">

      <input type="text" name="name" required
        placeholder="Full Name"
        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-200">

      <input type="text" name="roll_number" required
        placeholder="Roll Number"
        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-200">

      <input type="text" name="class"
        placeholder="Class"
        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-200">

      <input type="text" name="section"
        placeholder="Section"
        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-200">

      <div class="flex justify-end gap-3 pt-4">
        <button type="button" id="cancelModal"
          class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
          Cancel
        </button>
        <button type="submit"
          class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow">
          Save Student
        </button>
      </div>

    </form>
  </div>
</div>

<script>
const modal = document.getElementById('studentModal');
const openBtn = document.getElementById('openModal');
const closeBtn = document.getElementById('closeModal');
const cancelBtn = document.getElementById('cancelModal');

openBtn.addEventListener('click', () => {
  modal.classList.remove('hidden');
  modal.classList.add('flex');
});

function closeModal() {
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

closeBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);

modal.addEventListener('click', (e) => {
  if (e.target === modal) closeModal();
});
</script>

</body>
</html>

<?php $conn->close(); ?>