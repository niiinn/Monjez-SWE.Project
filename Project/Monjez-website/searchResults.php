<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: logIn.php");
    exit();
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Guest';
$searchTerm = trim($_GET['search'] ?? '');

$searchResults = [];

if (!empty($searchTerm)) {
    $likeTerm = "%" . $searchTerm . "%";
    $stmt = $conn->prepare("SELECT Task_Content FROM Tasks WHERE UserID = ? AND Task_Content LIKE ?");
    $stmt->bind_param("is", $userID, $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row['Task_Content'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - Monjez</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <img src="images/logo.png" alt="Logo" class="logo">
    <div class="search-box">
        <form method="GET" action="searchResults.php" onsubmit="return validateSearchForm()">
            <input type="text" id="searchInput" name="search" placeholder="Search tasks..." 
                   value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" style="display:none;">Search</button>
        </form>
    </div>
    <a href="MyTask.php"><img src="images/HP.png" alt="Home" class="home-logo"></a>
</header>

<div class="container">
    <div class="sidebar">
        <div class="profile-container">
            <img src="images/pro.png" alt="Profile">
            <span>Hello, <?= htmlspecialchars($username) ?></span>
        </div>
        <div>
            <a href="MyTask.php?view=all">☀️ My Tasks</a>
            <a href="ShoppingList.php">🛍️ Shopping List</a>
        </div>
        <form method="POST" action="logOut.php">
            <button class="logout">🔓 Logout</button>
        </form>
    </div>

    <div class="content">
        <div class="centered-search-container">
            <div class="search-results-box">
                <h2 style="margin-top: 0; color: #333;">🔍 Results for: <?= htmlspecialchars($searchTerm) ?></h2>

                <?php if (count($searchResults) > 0): ?>
                    <?php foreach ($searchResults as $task): ?>
                        <div class="search-task-box"><?= htmlspecialchars($task) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-results">No matching tasks found!</p>
                <?php endif; ?>

                <a href="MyTask.php?view=all" class="back-to-tasks">Back to Tasks</a>
            </div>
        </div>
    </div>
</div>

<footer>
    &copy; 2025 MONJEZ
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchInput");

    if (searchInput) {
        searchInput.addEventListener("focus", function() {
            if (!window.location.href.includes("searchResults.php")) {
                window.location.href = "searchResults.php";
            }
        });
    }
});

function validateSearchForm() {
    const keyword = document.getElementById("searchInput").value.trim();
    if (keyword === "") {
        alert("Please enter a keyword to search.");
        return false;
    }
    return true;
}
</script>

</body>
</html>
