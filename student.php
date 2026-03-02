<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Attendance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col">

<!-- NAVBAR -->
<nav class="bg-white border-b border-gray-100 shadow-sm">
  <div class="container mx-auto px-6 py-4 flex items-center justify-between">
    <a href="index.php" class="text-xl font-semibold text-blue-600">
      Online Attendance
    </a>
    <a href="index.php" class="text-sm text-gray-600 hover:text-blue-600 transition">
      Back to Home
    </a>
  </div>
</nav>

<main class="flex-1 flex items-center justify-center px-6 py-16">

  <div class="w-full max-w-4xl">

    <!-- HEADER -->
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-gray-900">
        Student Attendance Portal
      </h2>
      <p class="text-gray-500 mt-3">
        Enter your course details to view attendance records.
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-10">

      <form id="getAttendance" class="grid md:grid-cols-2 gap-8">

        <!-- YEAR -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Year of Course
          </label>
          <select name="year"
            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none">
            <?php foreach(range(date('Y'),1983) as $r) echo '<option>'.$r.'</option>'; ?>
          </select>
        </div>

        <!-- SECTION -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Section
          </label>
          <select name="section"
            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none">
            <option>1</option>
            <option>2</option>
            <option>3</option>
          </select>
        </div>

        <!-- SUBJECT CODE -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Subject Code
          </label>
          <input type="text"
            name="code"
            placeholder="e.g. COE-216"
            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none">
          <p class="text-xs text-gray-500 mt-1">
            Format: DDD-NNN (Department - Number)
          </p>
        </div>

        <!-- ROLL NUMBER -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Roll Number
          </label>
          <input type="text"
            name="roll"
            placeholder="e.g. 262/CO/12"
            class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none">
          <p class="text-xs text-gray-500 mt-1">
            Format: NNN/DD/YY
          </p>
        </div>

        <!-- BUTTON -->
        <div class="md:col-span-2 pt-4">
          <button type="submit"
            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg transition shadow">
            Get Attendance Results
          </button>
        </div>

      </form>

    </div>

    <!-- OUTPUT SECTION -->
    <div id="output" class="mt-12"></div>

  </div>

</main>

<!-- FOOTER -->
<footer class="bg-gray-800 text-white text-center py-4 text-sm">
  © <?= date('Y') ?> Online Attendance
</footer>

</body>
</html>