<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../mylogin.php");
    exit();
}

include '../database.php';

$per_page = 7;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $per_page;

/* TOTAL USERS */
$total_stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $total_stmt->fetchColumn();

$total_pages = ceil($total_users / $per_page);

/* FETCH USERS */
$stmt = $pdo->prepare("
    SELECT *
    FROM users
    ORDER BY userid ASC
    LIMIT :offset, :per_page
");

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);

$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>User List</title>

<link rel="stylesheet" href="../schools.css">

<style>

.user-table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.user-table thead tr {
    background-color:#4CAF50;
    color:white;
}

.user-table th,
.user-table td {
    padding:12px 16px;
    text-align:left;
    border-bottom:1px solid #ddd;
}

.user-table tbody tr:nth-child(odd) {
    background-color:#f9f9f9;
}

.user-table tbody tr:nth-child(even) {
    background-color:#ffffff;
}

.btn-settings {
    background-color:#4CAF50;
    color:white;
    padding:6px 12px;
    border-radius:4px;
    text-decoration:none;
    font-size:0.85rem;
    margin-right:4px;
}

.btn-delete {
    background-color:#e53935;
    color:white;
    padding:6px 12px;
    border-radius:4px;
    text-decoration:none;
    font-size:0.85rem;
}

.pagenation {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:20px;
}

.pagenation a {
    padding:8px 18px;
    background-color:pink;
    color:black;
    border-radius:4px;
    text-decoration:none;
}

.pagenation a.disabled {
    background-color:#ccc;
    pointer-events:none;
}

</style>

</head>

<body>

<div class="rectangle1">

    <p class="namePage">
        USJR-SCHOOL MANAGEMENT SYSTEM
    </p>

    <div class="navMenu">
        <a href="../mydashboard.php">Home</a>
        <a href="../school/schools.php">Schools</a>
        <a href="../department/department.php">Departments</a>
        <a href="../programs/programs.php">Programs</a>
        <a href="../student/students.php">Students</a>
        <a href="../users/users.php">Users</a>
        <a href="../logout.php">Logout</a>
    </div>

</div>

<div class="main-content">

    <div class="topbar">
        <div class="topbar-title">User List</div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">
            User updated successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            User deleted successfully.
        </div>
    <?php endif; ?>

    <a href="users.php"
       class="btn btn-danger"
       style="margin-bottom:14px; display:inline-block;">Back</a>

    <table class="user-table">

        <thead>

            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>User Type</th>
                <th>User Role</th>
                <th>System Role</th>
                <th>Actions</th>
            </tr>

        </thead>

        <tbody>
            <?php if (empty($users)): ?>
            <tr>
                <td colspan="5">
                    <div class="empty-state">
                        <span class="icon">👤</span>
                        <p>No users found.</p>
                    </div>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['userid'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($user['usertype']) ?></span></td>
                <td><span class="badge badge-gray"><?= htmlspecialchars($user['userrole']) ?></span></td>
                <td>
                    <div class="actions-col">
                        <a href="editUser.php?id=<?= $user['userid'] ?>" class="btn btn-warning btn-xs">Edit</a>
                        <a href="deleteUser.php?id=<?= $user['userid'] ?>" class="btn btn-danger btn-xs"
                            onclick="return confirm('Delete: <?= addslashes($user['username']) ?>?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

</div>
</div>


</table>

    <div class="pagenation">

        <div>

            <a href="?page=<?= $page - 1 ?>"
               class="<?= $page <= 1 ? 'disabled' : '' ?>">

               Previous

            </a>

            &nbsp;

            <a href="?page=<?= $page + 1 ?>"
               class="<?= $page >= $total_pages ? 'disabled' : '' ?>">

               Next

            </a>

        </div>

    </div>

</div>

</body>
</html>