<?php
session_start();
include("connection.php");

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {

        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");

        if ($stmt) {

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $admin = $result->fetch_assoc();

                // Verify hashed password
                if (password_verify($password, $admin['password'])) {

                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['username'] = $admin['username'];

                    header("Location: index.php");
                    exit();

                } else {
                    $error = "Invalid username or password.";
                }

            } else {
                $error = "Invalid username or password.";
            }

            $stmt->close();

        } else {
            $error = "Database query error.";
        }

    } else {
        $error = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Employee Records</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial, sans-serif;
    background:linear-gradient(135deg,#2d3748,#1a202c);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}

.login-card{
    background:#fff;
    width:100%;
    max-width:400px;
    padding:40px 30px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.25);
}

.login-card h1{
    text-align:center;
    color:#2d3748;
    margin-bottom:8px;
}

.login-card p{
    text-align:center;
    color:#718096;
    margin-bottom:25px;
}

.form-group{
    margin-bottom:18px;
}

label{
    display:block;
    margin-bottom:6px;
    font-weight:bold;
    color:#4a5568;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #cbd5e0;
    border-radius:6px;
    font-size:14px;
}

input:focus{
    outline:none;
    border-color:#4c51bf;
}

.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:6px;
    background:#4c51bf;
    color:white;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
}

.btn:hover{
    background:#434190;
}

.error{
    background:#fed7d7;
    color:#c53030;
    padding:12px;
    border-left:4px solid #f56565;
    border-radius:6px;
    margin-bottom:15px;
}

.hint{
    margin-top:18px;
    text-align:center;
    color:#a0aec0;
    font-size:12px;
}
</style>

</head>
<body>

<div class="login-card">

    <h1>🔒 Admin Login</h1>
    <p>Employee Records Management System</p>

    <?php if (!empty($error)): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required autofocus>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn">
            Login
        </button>

    </form>

    <div class="hint">
        Default Login: admin / admin123
    </div>

</div>

</body>
</html>