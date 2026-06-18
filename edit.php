<?php
include("auth_check.php");
include("connection.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php"); exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$emp) { header("Location: index.php"); exit(); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname   = trim($_POST['fullname']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $position   = trim($_POST['position']);
    $salary     = trim($_POST['salary']);
    $date_hired = trim($_POST['date_hired']);

    if (empty($fullname))   $errors[] = "Full name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($department)) $errors[] = "Department is required.";
    if (empty($position))   $errors[] = "Position is required.";
    if (!is_numeric($salary) || $salary < 0) $errors[] = "Salary must be a valid positive number.";
    if (empty($date_hired)) $errors[] = "Date hired is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE employees SET fullname=?, email=?, department=?, position=?, salary=?, date_hired=? WHERE id=?");
        $stmt->bind_param("ssssdsi", $fullname, $email, $department, $position, $salary, $date_hired, $id);
        if ($stmt->execute()) {
            header("Location: index.php?msg=updated"); exit();
        } else {
            $errors[] = "Update failed. Email may already be taken.";
        }
        $stmt->close();
    }

    $emp['fullname']   = $fullname;
    $emp['email']      = $email;
    $emp['department'] = $department;
    $emp['position']   = $position;
    $emp['salary']     = $salary;
    $emp['date_hired'] = $date_hired;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f4f8; }
        header { background: #2d3748; color: white; padding: 16px 24px; }
        nav { background: #1a202c; padding: 0 24px; }
        nav a { color: #cbd5e0; text-decoration: none; padding: 12px 16px; display: inline-block; font-size: 0.9rem; }
        nav a:hover { color: white; background: rgba(255,255,255,0.07); }
        .container { max-width: 560px; margin: 24px auto; padding: 0 16px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #fffbeb; }
        .card-body { padding: 24px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: bold; color: #4a5568; }
        input, select { width: 100%; padding: 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; }
        input:focus, select:focus { border-color: #d69e2e; outline: none; }
        .alert-error { background: #fed7d7; color: #9b2c2c; border-left: 4px solid #fc8181; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
        .alert-error ul { margin-left: 18px; margin-top: 4px; }
        .btn { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.9rem; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-warning { background: #d69e2e; color: white; }
        .btn-outline { background: white; color: #4a5568; border: 1.5px solid #ccc; margin-left: 8px; }
        .meta { font-size: 0.8rem; color: #aaa; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f0f4f8; }

        @media (max-width: 500px) { .container { padding: 0 10px; } }
    </style>
</head>
<body>
<header><h1>🏢 Employee Records Management</h1></header>
<nav>
    <a href="index.php">📋 All Employees</a>
    <a href="add.php">➕ Add Employee</a>
</nav>
<div class="container">
    <div class="card">
        <div class="card-header"><h2>✏️ Edit — <?= htmlspecialchars($emp['fullname']) ?></h2></div>
        <div class="card-body">

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <strong>Fix the following:</strong>
                    <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($emp['fullname']) ?>">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>">
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department">
                        <?php foreach (['IT','HR','Finance','Marketing','Operations','Sales'] as $d): ?>
                            <option value="<?= $d ?>" <?= ($emp['department'] === $d) ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Position *</label>
                    <input type="text" name="position" value="<?= htmlspecialchars($emp['position']) ?>">
                </div>
                <div class="form-group">
                    <label>Salary (KES) *</label>
                    <input type="number" step="0.01" name="salary" value="<?= htmlspecialchars($emp['salary']) ?>">
                </div>
                <div class="form-group">
                    <label>Date Hired *</label>
                    <input type="date" name="date_hired" value="<?= htmlspecialchars($emp['date_hired']) ?>">
                </div>
                <button type="submit" class="btn btn-warning">💾 Update Employee</button>
                <a href="index.php" class="btn btn-outline">Cancel</a>
            </form>

            <div class="meta">ID: #<?= $emp['id'] ?> | Added: <?= date('d M Y', strtotime($emp['created_at'])) ?></div>
        </div>
    </div>
</div>
</body>
</html>