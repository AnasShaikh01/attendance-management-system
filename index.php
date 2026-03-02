<?php
session_start();

if (isset($_SESSION['teacher_id'])) {
    header('Location: teacher.php');
    exit();
}

$errors  = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? null;
$old     = $_SESSION['old'] ?? [];

unset($_SESSION['errors']);
unset($_SESSION['success']);
unset($_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Online Attendance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col">

<!-- NAVBAR -->
<nav class="bg-white border-b border-gray-100">
  <div class="container mx-auto px-6 py-4 flex items-center justify-between">
    <h1 class="text-xl font-semibold text-blue-600 tracking-tight">
      Online Attendance
    </h1>

    <div class="flex items-center gap-6 text-sm font-medium">
      <a href="student.php"
         class="text-gray-600 hover:text-green-600 transition">
        Student Portal
      </a>
    </div>
  </div>
</nav>

<main class="flex-1 flex items-center justify-center px-6 py-16">

  <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-20 items-center">

    <!-- LEFT SIDE (Hero Section) -->
    <div>

      <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight">
        A Modern Way to Manage Attendance
      </h2>

      <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-lg">
        Designed for institutions that value simplicity,
        accuracy, and structured academic management.
      </p>

      <!-- Student Access Inline -->
      <div class="mt-10">
        <a href="student.php"
           class="inline-flex items-center text-green-600 font-medium hover:text-green-700 transition">
          Access Student Dashboard →
        </a>
      </div>

    </div>

    <!-- RIGHT SIDE (Faculty Card) -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-10">

      <h3 class="text-xl font-semibold text-gray-900 text-center mb-8">
        Faculty Access
      </h3>

      <!-- FLASH MESSAGES -->
      <?php if (!empty($errors)): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-600 text-sm">
          <?php foreach ($errors as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-50 text-green-600 text-sm">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <!-- TABS -->
      <div class="flex bg-gray-100 rounded-full p-1 mb-8">
        <button id="loginTab"
          class="flex-1 py-2 rounded-full text-sm font-medium transition">
          Login
        </button>
        <button id="signupTab"
          class="flex-1 py-2 rounded-full text-sm font-medium transition">
          Sign Up
        </button>
      </div>

      <!-- LOGIN FORM -->
      <form id="loginForm" class="space-y-5" method="POST" action="php/process_login.php">

        <input name="email" type="email" required
          value="<?= htmlspecialchars($old['email'] ?? '') ?>"
          class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none"
          placeholder="Email address">

        <input name="password" type="password" required
          class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none"
          placeholder="Password">

        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg transition">
          Login
        </button>
      </form>

      <!-- SIGNUP FORM -->
      <form id="signupForm" class="hidden space-y-5" method="POST" action="php/process_signup.php">

        <input name="name" type="text" required
          value="<?= htmlspecialchars($old['name'] ?? '') ?>"
          class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-200 focus:outline-none"
          placeholder="Full Name">

        <input name="phone" type="text"
          value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
          class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-200 focus:outline-none"
          placeholder="Phone Number">

        <input name="email" type="email" required
          value="<?= htmlspecialchars($old['email'] ?? '') ?>"
          class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-200 focus:outline-none"
          placeholder="Email address">

        <input name="password" type="password" required
          class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-200 focus:outline-none"
          placeholder="Password">

        <input name="password2" type="password" required
          class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-200 focus:outline-none"
          placeholder="Confirm Password">

        <button type="submit"
          class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg transition">
          Create Account
        </button>
      </form>

    </div>

  </div>

</main>

<script>
const loginTab = document.getElementById('loginTab');
const signupTab = document.getElementById('signupTab');
const loginForm = document.getElementById('loginForm');
const signupForm = document.getElementById('signupForm');

function showTab(tab) {
  if (tab === 'signup') {
    signupForm.classList.remove('hidden');
    loginForm.classList.add('hidden');
    signupTab.classList.add('bg-white');
    loginTab.classList.remove('bg-white');
  } else {
    loginForm.classList.remove('hidden');
    signupForm.classList.add('hidden');
    loginTab.classList.add('bg-white');
    signupTab.classList.remove('bg-white');
  }
}

const params = new URLSearchParams(window.location.search);
const initialTab = params.get('tab') === 'signup' ? 'signup' : 'login';
showTab(initialTab);

loginTab.addEventListener('click', () => showTab('login'));
signupTab.addEventListener('click', () => showTab('signup'));
</script>

</body>
</html>