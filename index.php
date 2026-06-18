<?php
include("auth_check.php");
include("connection.php");

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?msg=deleted");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE fullname LIKE ? OR department LIKE ? OR position LIKE ? ORDER BY created_at DESC");
    $likeTerm = "%$search%";
    $stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, "SELECT * FROM employees ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Records Management</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f4f8; }
        header { background: #2d3748; color: white; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        header h1 { font-size: 1.2rem; }
        .user-info { display: flex; align-items: center; gap: 12px; font-size: 0.85rem; }
        .logout-link { color: #fc8181; text-decoration: none; font-weight: bold; }
        nav { background: #1a202c; padding: 0 24px; }
        nav a { color: #cbd5e0; text-decoration: none; padding: 12px 16px; display: inline-block; font-size: 0.9rem; }
        nav a:hover { color: white; background: rgba(255,255,255,0.07); }
        .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 18px; }
        .success { background: #c6f6d5; color: #276749; border-left: 4px solid #48bb78; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
        .card-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .search-box { display: flex; gap: 6px; }
        .search-box input { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 0.85rem; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th { background: #edf2f7; padding: 12px 16px; text-align: left; font-size: 0.78rem; color: #2d3748; text-transform: uppercase; }
        td { padding: 12px 16px; border-bottom: 1px solid #f0f4f8; font-size: 0.87rem; }
        tr:hover td { background: #f7fafc; }
        .btn { padding: 7px 14px; border-radius: 5px; border: none; cursor: pointer; font-size: 0.8rem; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-primary { background: #4c51bf; color: white; }
        .btn-warning { background: #d69e2e; color: white; }
        .btn-danger  { background: #e53e3e; color: white; }
        .badge { background: #e9d8fd; color: #553c9a; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: bold; }

        @media (max-width: 600px) {
            header { flex-direction: column; align-items: flex-start; }
            .card-header { flex-direction: column; align-items: stretch; }
            .search-box { flex-direction: column; }
        }
    </style>
</head>
<body>
<header>
    <h1>🏢 Employee Records Management</h1>
    <div class="user-info">
        👤 <?= htmlspecialchars($_SESSION['username']) ?>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
</header>
<nav>
    <a href="index.php">📋 All Employees</a>
    <a href="add.php">➕ Add Employee</a>
</nav>
<div class="container">
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert success">
            <?php
            if ($_GET['msg'] === 'added')   echo '✅ Employee added successfully.';
            if ($_GET['msg'] === 'updated') echo '✅ Employee updated successfully.';
            if ($_GET['msg'] === 'deleted') echo '🗑️ Employee deleted.';
            ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Employee Directory</h2>
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Search name, dept, position..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">🔍 Search</button>
            </form>
            <a href="add.php" class="btn btn-primary">+ Add Employee</a>
        </div>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Full Name</th><th>Email</th><th>Department</th><th>Position</th><th>Salary</th><th>Date Hired</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) > 0):
                $i = 1;
                while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($row['department']) ?></span></td>
                    <td><?= htmlspecialchars($row['position']) ?></td>
                    <td>KES <?= number_format($row['salary'], 2) ?></td>
                    <td><?= date('d M Y', strtotime($row['date_hired'])) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning">✏️ Edit</a>
                        <a href="index.php?delete=<?= $row['id'] ?>" class="btn btn-danger"
                           onclick="return confirm('Delete this employee record?')">🗑️ Delete</a>
                    </td>
                </tr>
                <?php endwhile;
            else: ?>
                <tr><td colspan="8" style="text-align:center; padding:30px; color:#aaa;">No employees found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</body>
</html>