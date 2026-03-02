<?php
session_start();

/* Destroy Session Securely */
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

/* Prevent Back Button Cache */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="refresh" content="2;url=index.php">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white rounded-2xl shadow-md border border-gray-100 p-10 text-center max-w-md w-full">

    <div class="h-16 w-16 mx-auto bg-green-100 text-green-600 rounded-full flex items-center justify-center text-2xl mb-6">
        ✓
    </div>

    <h2 class="text-2xl font-semibold text-gray-800 mb-2">
        Logged Out Successfully
    </h2>

    <p class="text-gray-500 text-sm mb-6">
        You have been securely signed out of your account.
    </p>

    <div class="text-sm text-gray-400">
        Redirecting to login page...
    </div>

    <div class="mt-6">
        <a href="index.php"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow transition">
            Go to Login Now
        </a>
    </div>

</div>

</body>
</html>