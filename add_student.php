<?php
session_start();
if (!(isset($_SESSION['teacher_id']))) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2>Add New Student</h2>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Student added successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <form method="POST" action="php/add_student_process.php">
        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" class="form-control" required placeholder="John Doe">
        </div>
        <div class="form-group">
            <label>Roll Number:</label>
            <input type="text" name="roll_number" class="form-control" required placeholder="262/CO/12">
        </div>
        <div class="form-group">
            <label>Class:</label>
            <input type="text" name="class" class="form-control" placeholder="CSE, BTech, etc.">
        </div>
        <div class="form-group">
            <label>Section:</label>
            <input type="text" name="section" class="form-control" placeholder="A / B / 1 / 2">
        </div>
        <button type="submit" class="btn btn-primary">Add Student</button>
        <a href="teacher.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
