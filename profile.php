<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

require_once 'php/node_class.php';

$name = $_SESSION['name'];
$phone = $_SESSION['phone'];
$email = $_SESSION['email'];
$teacher_id = $_SESSION['teacher_id'];
$classes = $_SESSION['classes'] ?? [];

$totalClasses = $classes ? count($classes) : 0;
$totalSessions = 0;
$totalStudents = 0;

if ($classes) {
    foreach ($classes as $c) {
        $node = Node::retrieveObjecti($c, $teacher_id);
        if (!$node) continue;

        $totalSessions += $node->getDays();
        $totalStudents += count($node->getRecords());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
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
      <a href="statistics.php" class="hover:text-blue-600 transition">Statistics</a>
      <a href="profile.php" class="text-blue-600">Profile</a>
      <a href="logout.php" class="text-red-500 hover:text-red-600 transition">Logout</a>
    </div>
  </div>
</nav>

<main class="flex-1 container mx-auto px-6 py-12">

<!-- HEADER -->
<div class="mb-12">
    <h2 class="text-3xl font-bold text-gray-800">
        Account Overview
    </h2>
    <p class="text-gray-500 mt-2">
        Monitor your teaching activity and account information.
    </p>
</div>

<!-- PROFILE HERO CARD -->
<div class="bg-white rounded-2xl shadow-md border border-gray-100 p-10 mb-12">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">

        <!-- LEFT SECTION -->
        <div class="flex items-center gap-6">

            <div class="h-20 w-20 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold shadow">
                <?= strtoupper(substr($name,0,1)) ?>
            </div>

            <div>
                <h3 class="text-2xl font-semibold text-gray-800">
                    <?= htmlspecialchars($name) ?>
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Teacher ID: <?= $teacher_id ?>
                </p>
            </div>

        </div>

        <!-- RIGHT SECTION -->
        <div class="grid sm:grid-cols-2 gap-6 text-sm text-gray-600">

            <div>
                <p class="text-gray-500">Email</p>
                <p class="font-medium text-gray-800 mt-1">
                    <?= htmlspecialchars($email) ?>
                </p>
            </div>

            <div>
                <p class="text-gray-500">Phone</p>
                <p class="font-medium text-gray-800 mt-1">
                    <?= htmlspecialchars($phone) ?>
                </p>
            </div>

        </div>

    </div>

</div>

<!-- STATISTICS GRID -->
<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">

    <!-- CARD 1 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">

        <div class="h-1 w-12 bg-blue-600 rounded mb-4"></div>

        <p class="text-sm text-gray-500 mb-1">Total Classes</p>
        <p class="text-3xl font-bold text-gray-800">
            <?= $totalClasses ?>
        </p>

    </div>

    <!-- CARD 2 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">

        <div class="h-1 w-12 bg-blue-600 rounded mb-4"></div>

        <p class="text-sm text-gray-500 mb-1">Sessions Conducted</p>
        <p class="text-3xl font-bold text-gray-800">
            <?= $totalSessions ?>
        </p>

    </div>

    <!-- CARD 3 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">

        <div class="h-1 w-12 bg-blue-600 rounded mb-4"></div>

        <p class="text-sm text-gray-500 mb-1">Students Managed</p>
        <p class="text-3xl font-bold text-gray-800">
            <?= $totalStudents ?>
        </p>

    </div>

</div>

</main>

</body>
</html>