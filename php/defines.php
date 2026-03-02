<?php

/* ==============================
   DATABASE CONFIGURATION
================================= */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'attendance');

/* ==============================
   DATABASE CONNECTION
================================= */

function connectTo() {

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $con->set_charset("utf8mb4");
        return $con;

    } catch (mysqli_sql_exception $e) {

        error_log("Database connection failed: " . $e->getMessage());

        die("System temporarily unavailable."); 
        // Never expose raw DB errors in production
    }
}

/* ==============================
   VALIDATION HELPERS
================================= */

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone) === 1;
}

function validateName($name) {
    return preg_match('/^[a-zA-Z\s\']+$/', $name) === 1;
}

/* ==============================
   SESSION LOADER
================================= */

function loadTeacherSession($teacherId) {

    if (!is_numeric($teacherId)) {
        return false;
    }

    $con = connectTo();

    try {

        $stmt = $con->prepare(
            "SELECT name, email, phone 
             FROM teacher 
             WHERE uid = ?"
        );

        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$user = $result->fetch_assoc()) {
            $stmt->close();
            $con->close();
            return false;
        }

        $_SESSION['teacher_id'] = $teacherId;
        $_SESSION['name']       = $user['name'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['phone']      = $user['phone'];
        $_SESSION['classes']    = [];

        $stmt2 = $con->prepare(
            "SELECT uid 
             FROM objects 
             WHERE teacher_uid = ? 
             ORDER BY uid DESC"
        );

        $stmt2->bind_param("i", $teacherId);
        $stmt2->execute();
        $classes = $stmt2->get_result();

        while ($row = $classes->fetch_assoc()) {
            $_SESSION['classes'][] = $row['uid'];
        }

        $stmt2->close();
        $stmt->close();
        $con->close();

        return true;

    } catch (Exception $e) {

        error_log("Session load error: " . $e->getMessage());
        $con->close();
        return false;
    }
}
?>