<?php
session_start();

include 'db_connection.php';


$colorTheme = $_SESSION['color_theme'] ?? 'default-color';

// Check if a new color theme is set
if (isset($_POST['color_theme'])) {
    $colorTheme = $_POST['color_theme'];
    $_SESSION['color_theme'] = $colorTheme; 
    setcookie('color_theme', $colorTheme, time() + (86400 * 30), "/");
}


if (!isset($_SESSION['user_id'])) {
    header("Location: logIn.php");
    exit();
}

$userID = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Guest';

$showAll = isset($_GET['view']) && $_GET['view'] === 'all';

if (isset($_POST['add_task'])) {
    $content = trim($_POST['task_content']);
    $priority = $_POST['priority'];
    $dueDate = $_POST['due_date'] ?: null;

    if (!empty($content) && !empty($priority)) {
        $stmt = $conn->prepare("INSERT INTO Tasks (UserID, Task_Content, Priority, Due_Date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $userID, $content, $priority, $dueDate);
        $stmt->execute();
    }
}

if (isset($_POST['delete_task'])) {
    $taskID = $_POST['task_id'];
    $stmt = $conn->prepare("DELETE FROM Tasks WHERE TaskID = ? AND UserID = ?");
    $stmt->bind_param("ii", $taskID, $userID);
    $stmt->execute();
}

if (isset($_POST['toggle_complete'])) {
    $taskID = $_POST['task_id'];
    $stmt = $conn->prepare("UPDATE Tasks SET Completed = NOT Completed WHERE TaskID = ? AND UserID = ?");
    $stmt->bind_param("ii", $taskID, $userID);
    $stmt->execute();
}

if (isset($_POST['update_task'])) {
    $taskID = $_POST['task_id'];
    $updatedContent = trim($_POST['updated_content']);
    $updatedPriority = $_POST['updated_priority'];
    $updatedDate = $_POST['updated_date'] ?: null;

    $stmt = $conn->prepare("UPDATE Tasks SET Task_Content = ?, Priority = ?, Due_Date = ? WHERE TaskID = ? AND UserID = ?");
    $stmt->bind_param("sssii", $updatedContent, $updatedPriority, $updatedDate, $taskID, $userID);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT * FROM Tasks WHERE UserID = ? ORDER BY Completed ASC, Priority DESC, Due_Date ASC");
$stmt->bind_param("i", $userID);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate progress
$totalTasks = count($tasks);
$completedTasks = array_reduce($tasks, function($carry, $task) {
    return $carry + ($task['Completed'] ? 1 : 0);
}, 0);
$progressPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
$progressPercentageText = round($progressPercentage); // Round to nearest integer

// Calculate dates for three days before and after the current day
$dates = [];
$today = new DateTime();
for ($i = -3; $i <= 3; $i++) {
    $date = clone $today;
    $date->modify("$i day");
    $dates[] = [
        'day' => $date->format('d'),
        'month' => $date->format('F'),
        'isToday' => $i === 0
    ];
}


$themeImages = [
    'default-color' => [
        'tasks' =>"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACXElEQVR4nNWSzU8TQRjGe5FEL36csULa7ggoDeluW1GRlt2mmmpRaJdAd4kx6bl+HIzYetOL3gpalHZsEEP92mk1mBgSdk08EvxL7EVNifiambjbVlsD3HyTJ5l93uf97czs2mz/ZUlcsVvkcE5EeENCeCWEcGDIMR+XuMJ76tFewIkd24ThhITwdwlhMCVyhR9e+ywEnHnLo5kQKkz9EzaCCsMiwluNMFPDzidwoutRk8eyLjzUfncIr7SCMXEFEOzZVr13bYEiyn8OuBbYboKuBQZpHOY7s8wL/s7QrIjyG22BJ7vn10dceZCOYjjjeAy+I7Mw2JWjR2PydGbBZ59jvRDCEOTycKo7t94SpghkWhG0TVUgoPAaXOpfhgvHliDc8xSiAw8gOnAfzvYUmUd7NMOyglZT+LLSBJt2vzmg8uQbDVBFjz+3jjnln4NaeZRp0v/Q8mnGzCuC9jXpKe2vA73EbzapIn2L1uDVc4uwWbkItcoopMJ1/3zfMyuvCgQSHs1nARPeiruxOeZeZkOXTxNQvWVIiUtMdE092qOZxhnVR/otYNKT26MIpGodgddA5l9CKvyheUggzJP5F9YdquzIpEoZTfeo8uRe4+D1yCrcGluDK4NvLY+uqXcjstr0EkXQ7v71lZOe8j6VJ4YZmhnXISMbTBRCZT7fHtfrMJ7oMX9pb8tfJ9Zb6kjw2oQiaNcyslE1AX8qHTe+0AzNxnpLHbbtVDquF9sCZQPbdlp3Yp8OZeL664ys/6zvTN9Kx41XNyc/Htwx0KwZWT+cnliTqOh616Dd1i+nhuUhZVHVJgAAAABJRU5ErkJggg==", // Default image for My Tasks
        'shopping' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACXElEQVR4nNWSzU8TQRjGe5FEL36csULa7ggoDeluW1GRlt2mmmpRaJdAd4kx6bl+HIzYetOL3gpalHZsEEP92mk1mBgSdk08EvxL7EVNifiambjbVlsD3HyTJ5l93uf97czs2mz/ZUlcsVvkcE5EeENCeCWEcGDIMR+XuMJ76tFewIkd24ThhITwdwlhMCVyhR9e+ywEnHnLo5kQKkz9EzaCCsMiwluNMFPDzidwoutRk8eyLjzUfncIr7SCMXEFEOzZVr13bYEiyn8OuBbYboKuBQZpHOY7s8wL/s7QrIjyG22BJ7vn10dceZCOYjjjeAy+I7Mw2JWjR2PydGbBZ59jvRDCEOTycKo7t94SpghkWhG0TVUgoPAaXOpfhgvHliDc8xSiAw8gOnAfzvYUmUd7NMOyglZT+LLSBJt2vzmg8uQbDVBFjz+3jjnln4NaeZRp0v/Q8mnGzCuC9jXpKe2vA73EbzapIn2L1uDVc4uwWbkItcoopMJ1/3zfMyuvCgQSHs1nARPeiruxOeZeZkOXTxNQvWVIiUtMdE092qOZxhnVR/otYNKT26MIpGodgddA5l9CKvyheUggzJP5F9YdquzIpEoZTfeo8uRe4+D1yCrcGluDK4NvLY+uqXcjstr0EkXQ7v71lZOe8j6VJ4YZmhnXISMbTBRCZT7fHtfrMJ7oMX9pb8tfJ9Zb6kjw2oQiaNcyslE1AX8qHTe+0AzNxnpLHbbtVDquF9sCZQPbdlp3Yp8OZeL664ys/6zvTN9Kx41XNyc/Htwx0KwZWT+cnliTqOh616Dd1i+nhuUhZVHVJgAAAABJRU5ErkJggg==", // Default image for Shopping List
        'logout' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACXElEQVR4nNWSzU8TQRjGe5FEL36csULa7ggoDeluW1GRlt2mmmpRaJdAd4kx6bl+HIzYetOL3gpalHZsEEP92mk1mBgSdk08EvxL7EVNifiambjbVlsD3HyTJ5l93uf97czs2mz/ZUlcsVvkcE5EeENCeCWEcGDIMR+XuMJ76tFewIkd24ThhITwdwlhMCVyhR9e+ywEnHnLo5kQKkz9EzaCCsMiwluNMFPDzidwoutRk8eyLjzUfncIr7SCMXEFEOzZVr13bYEiyn8OuBbYboKuBQZpHOY7s8wL/s7QrIjyG22BJ7vn10dceZCOYjjjeAy+I7Mw2JWjR2PydGbBZ59jvRDCEOTycKo7t94SpghkWhG0TVUgoPAaXOpfhgvHliDc8xSiAw8gOnAfzvYUmUd7NMOyglZT+LLSBJt2vzmg8uQbDVBFjz+3jjnln4NaeZRp0v/Q8mnGzCuC9jXpKe2vA73EbzapIn2L1uDVc4uwWbkItcoopMJ1/3zfMyuvCgQSHs1nARPeiruxOeZeZkOXTxNQvWVIiUtMdE092qOZxhnVR/otYNKT26MIpGodgddA5l9CKvyheUggzJP5F9YdquzIpEoZTfeo8uRe4+D1yCrcGluDK4NvLY+uqXcjstr0EkXQ7v71lZOe8j6VJ4YZmhnXISMbTBRCZT7fHtfrMJ7oMX9pb8tfJ9Zb6kjw2oQiaNcyslE1AX8qHTe+0AzNxnpLHbbtVDquF9sCZQPbdlp3Yp8OZeL664ys/6zvTN9Kx41XNyc/Htwx0KwZWT+cnliTqOh616Dd1i+nhuUhZVHVJgAAAABJRU5ErkJggg==", // Default image for Logout
    ],
    'blue-theme' => [
        'tasks' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACIklEQVR4nNWSy4oTQRSGs3FAN172LsS9LnUluvAVRFDxHXwIfQZ3kr2CpnMzQyZRugchjOM4pON0MqixK0knfak+pxIUxyNVSd+cROLsLPih6j9/fV2nqnO5/3K87AwuaV32VLPYbqFrlzVrcEv75tzRuqwiPVkrHYwurwXTDtj9gsVmWpdRpELX/lnxAip+GSWexWaFnn3vr7CCNbipWfZRGhap9NWhysTP+jL7id1YDZTtLYEp9RhVeXjct+zi6nYP2QfZVqnvUPHzUEHSmxWwx1RNZeQV9NjuSmDV9Xa2BFBDANUFUA2Ayo47B/cYvYa5V19kZLbiejtLYQbyhzrwHwaGpENI9eGEarZDNcelcgBU5kCbjqs8WZMZmTWAf9eRP8jAtnz/nA58qgIY0iYbx22+OhxS3psqyXnk19h4DkR1ANHyvLMx8K3g16OiVLWf/B7NcUB5f6bUcILkPvujOG9gSNuCX0uAiFfTxfrIVZsMx1frJsyl6o6najKTAQJciYEtolM6hkEcAE6NwYTeI2Q2SUmvMZyoTNwyhoFkZB8F+JP0xo+I1EakdylPzqW3h5j5iA788bFXbpF9xkD+JgqZKKizUHuh9DoB8qZBdHrpr7NPtKED3N0G/shEEUSAP2Wi8GVGZveJNnLrDBNEfhWwA+LZWpBc+rRBcMEEfGEC/opPBnjUQXy+FwTn/xkYDWs6vdgGuC0l5ycGnXT8Bjb6JBuG3FomAAAAAElFTkSuQmCC" ,
        'shopping' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACIklEQVR4nNWSy4oTQRSGs3FAN172LsS9LnUluvAVRFDxHXwIfQZ3kr2CpnMzQyZRugchjOM4pON0MqixK0knfak+pxIUxyNVSd+cROLsLPih6j9/fV2nqnO5/3K87AwuaV32VLPYbqFrlzVrcEv75tzRuqwiPVkrHYwurwXTDtj9gsVmWpdRpELX/lnxAip+GSWexWaFnn3vr7CCNbipWfZRGhap9NWhysTP+jL7id1YDZTtLYEp9RhVeXjct+zi6nYP2QfZVqnvUPHzUEHSmxWwx1RNZeQV9NjuSmDV9Xa2BFBDANUFUA2Ayo47B/cYvYa5V19kZLbiejtLYQbyhzrwHwaGpENI9eGEarZDNcelcgBU5kCbjqs8WZMZmTWAf9eRP8jAtnz/nA58qgIY0iYbx22+OhxS3psqyXnk19h4DkR1ANHyvLMx8K3g16OiVLWf/B7NcUB5f6bUcILkPvujOG9gSNuCX0uAiFfTxfrIVZsMx1frJsyl6o6najKTAQJciYEtolM6hkEcAE6NwYTeI2Q2SUmvMZyoTNwyhoFkZB8F+JP0xo+I1EakdylPzqW3h5j5iA788bFXbpF9xkD+JgqZKKizUHuh9DoB8qZBdHrpr7NPtKED3N0G/shEEUSAP2Wi8GVGZveJNnLrDBNEfhWwA+LZWpBc+rRBcMEEfGEC/opPBnjUQXy+FwTn/xkYDWs6vdgGuC0l5ycGnXT8Bjb6JBuG3FomAAAAAElFTkSuQmCC",
        'logout' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACIklEQVR4nNWSy4oTQRSGs3FAN172LsS9LnUluvAVRFDxHXwIfQZ3kr2CpnMzQyZRugchjOM4pON0MqixK0knfak+pxIUxyNVSd+cROLsLPih6j9/fV2nqnO5/3K87AwuaV32VLPYbqFrlzVrcEv75tzRuqwiPVkrHYwurwXTDtj9gsVmWpdRpELX/lnxAip+GSWexWaFnn3vr7CCNbipWfZRGhap9NWhysTP+jL7id1YDZTtLYEp9RhVeXjct+zi6nYP2QfZVqnvUPHzUEHSmxWwx1RNZeQV9NjuSmDV9Xa2BFBDANUFUA2Ayo47B/cYvYa5V19kZLbiejtLYQbyhzrwHwaGpENI9eGEarZDNcelcgBU5kCbjqs8WZMZmTWAf9eRP8jAtnz/nA58qgIY0iYbx22+OhxS3psqyXnk19h4DkR1ANHyvLMx8K3g16OiVLWf/B7NcUB5f6bUcILkPvujOG9gSNuCX0uAiFfTxfrIVZsMx1frJsyl6o6najKTAQJciYEtolM6hkEcAE6NwYTeI2Q2SUmvMZyoTNwyhoFkZB8F+JP0xo+I1EakdylPzqW3h5j5iA788bFXbpF9xkD+JgqZKKizUHuh9DoB8qZBdHrpr7NPtKED3N0G/shEEUSAP2Wi8GVGZveJNnLrDBNEfhWwA+LZWpBc+rRBcMEEfGEC/opPBnjUQXy+FwTn/xkYDWs6vdgGuC0l5ycGnXT8Bjb6JBuG3FomAAAAAElFTkSuQmCC",
    ],
    'pink-theme' => [
        'tasks' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACR0lEQVR4nNWSy27TQBSGs6ESbLjsWSD2sIQVggWvgJAA8Q48BCyRnQUpEECFAnVKpUpAbk3tNFNIIZSktaLGjp3MOE5JUeMJDkUgykEzblyHplHbHUf6Jfs///k0t1Dov6wVMXtKE9GoLqCiJqK4LqBLZnThiibkElveqCag03uCaSK6rgnohy4i8CWg3w2pDEZkwfdYRhPQtaEwPZy7qIm5zT7YlswHH8AaV/s8lq3cnb8wbHXxQTCmahiBPbmyw9fE3JshK0QlI5IH8/5HMO7lOSQ4bE9WuMd6PBPJgx5GxV2B1kR5cS1eg6+JOrTemrA6rQMeK0E1PM9lT+mwOl3lPZZhWUsqLw6EtbP4pjNLflGFgJOpgy0tA3n6GeyXKjSlCldjQvU8aZlneFYmPzty/UYfzJFrx6iMN1iAqfGs6G/TCL+H9YcaF/vu+Y3xEs9SJhl/b6eNoz6wI1vn/aZCAD8q+INrsTK0ozqsR3Voxcq+jx9/2gYqBDqz1rnt7Wass8FmM+Y9jy8x1fNmsCeFQEvyeiwTnGnPWWd8IBQKh6hCaK/pyBiaL5bgW6bWN8TEvObzJZ4J+JQx+s9RIXeCg+5MHdy0yc4neFbcc9PehfgLUMjtHbdsF+wjVCZzvVA3VYNuyuRy0zWu3n83HVi5jLPkHTk88OmAqo5QmVx1FHyrmzSpD/hXSdNhGZYFVR0J7aXcpDG2G9BNGU9C+y2aICfclDHlJo0/AdBmN2m+oq/x8X0De7WR0E+6CeMyE/s+MOig9RdpUPgvv6LU5QAAAABJRU5ErkJggg==",
        'shopping' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACR0lEQVR4nNWSy27TQBSGs6ESbLjsWSD2sIQVggWvgJAA8Q48BCyRnQUpEECFAnVKpUpAbk3tNFNIIZSktaLGjp3MOE5JUeMJDkUgykEzblyHplHbHUf6Jfs///k0t1Dov6wVMXtKE9GoLqCiJqK4LqBLZnThiibkElveqCag03uCaSK6rgnohy4i8CWg3w2pDEZkwfdYRhPQtaEwPZy7qIm5zT7YlswHH8AaV/s8lq3cnb8wbHXxQTCmahiBPbmyw9fE3JshK0QlI5IH8/5HMO7lOSQ4bE9WuMd6PBPJgx5GxV2B1kR5cS1eg6+JOrTemrA6rQMeK0E1PM9lT+mwOl3lPZZhWUsqLw6EtbP4pjNLflGFgJOpgy0tA3n6GeyXKjSlCldjQvU8aZlneFYmPzty/UYfzJFrx6iMN1iAqfGs6G/TCL+H9YcaF/vu+Y3xEs9SJhl/b6eNoz6wI1vn/aZCAD8q+INrsTK0ozqsR3Voxcq+jx9/2gYqBDqz1rnt7Wass8FmM+Y9jy8x1fNmsCeFQEvyeiwTnGnPWWd8IBQKh6hCaK/pyBiaL5bgW6bWN8TEvObzJZ4J+JQx+s9RIXeCg+5MHdy0yc4neFbcc9PehfgLUMjtHbdsF+wjVCZzvVA3VYNuyuRy0zWu3n83HVi5jLPkHTk88OmAqo5QmVx1FHyrmzSpD/hXSdNhGZYFVR0J7aXcpDG2G9BNGU9C+y2aICfclDHlJo0/AdBmN2m+oq/x8X0De7WR0E+6CeMyE/s+MOig9RdpUPgvv6LU5QAAAABJRU5ErkJggg==",
        'logout' =>"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACR0lEQVR4nNWSy27TQBSGs6ESbLjsWSD2sIQVggWvgJAA8Q48BCyRnQUpEECFAnVKpUpAbk3tNFNIIZSktaLGjp3MOE5JUeMJDkUgykEzblyHplHbHUf6Jfs///k0t1Dov6wVMXtKE9GoLqCiJqK4LqBLZnThiibkElveqCag03uCaSK6rgnohy4i8CWg3w2pDEZkwfdYRhPQtaEwPZy7qIm5zT7YlswHH8AaV/s8lq3cnb8wbHXxQTCmahiBPbmyw9fE3JshK0QlI5IH8/5HMO7lOSQ4bE9WuMd6PBPJgx5GxV2B1kR5cS1eg6+JOrTemrA6rQMeK0E1PM9lT+mwOl3lPZZhWUsqLw6EtbP4pjNLflGFgJOpgy0tA3n6GeyXKjSlCldjQvU8aZlneFYmPzty/UYfzJFrx6iMN1iAqfGs6G/TCL+H9YcaF/vu+Y3xEs9SJhl/b6eNoz6wI1vn/aZCAD8q+INrsTK0ozqsR3Voxcq+jx9/2gYqBDqz1rnt7Wass8FmM+Y9jy8x1fNmsCeFQEvyeiwTnGnPWWd8IBQKh6hCaK/pyBiaL5bgW6bWN8TEvObzJZ4J+JQx+s9RIXeCg+5MHdy0yc4neFbcc9PehfgLUMjtHbdsF+wjVCZzvVA3VYNuyuRy0zWu3n83HVi5jLPkHTk88OmAqo5QmVx1FHyrmzSpD/hXSdNhGZYFVR0J7aXcpDG2G9BNGU9C+y2aICfclDHlJo0/AdBmN2m+oq/x8X0De7WR0E+6CeMyE/s+MOig9RdpUPgvv6LU5QAAAABJRU5ErkJggg==" ,
    ],
];

$currentThemeImages = $themeImages[$colorTheme];

$imageSrc = "images/1.png"; // Default image

if ($colorTheme === 'blue-theme') {
    $imageSrc = "images/2.png"; // Image for blue theme
} elseif ($colorTheme === 'pink-theme') {
    $imageSrc = "images/3.png"; // Image for pink theme
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tasks - Monjez</title>
    <link rel="stylesheet" href="style.css">
    <style>
       /* Default Color Theme */
        body {
            background-color: white;
            color: black; /* Default text color */
        }

        /* Blue Theme */
        header.blue-theme, footer.blue-theme, .high-priority-tasks.blue-theme {
            background-color: #D6EEF9;
             border-left: #A594F9;

        }

       

        /* Green Theme */
         header.pink-theme, footer.pink-theme, .high-priority-tasks.pink-theme {
            background-color: #FFE3E1;
                        border-left:  #A594F9;

        }

        
        
        
        

        .task-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            border-collapse: separate;
            border-spacing: 0 25px; /* Space between rows */
        }

        .task-table tr {
            height: 60px;
        }

        .task-table td {
            padding: 15px 30px;
            text-align: center;
            border: none;
            background: #f9f9f9; /* Light background for rows */
        }

        .task-table tr:nth-child(odd) td {
            background-color: #E5D9F2; /* Light pink */
        }

        .task-table tr:nth-child(even) td {
            background-color: #F5EFFF; /* Light purple */
        }
        
        
        .task-table.blue-theme tr:nth-child(odd) td {
            background-color: #D6EEF9; /* Light pink */
        }

        .task-table.blue-theme tr:nth-child(even) td {
            background-color: #F2FAFE; /* Light purple */
        }

        
        .task-table.pink-theme tr:nth-child(odd) td {
            background-color: #FFF4F2; /* Light pink */
        }

        .task-table.pink-theme tr:nth-child(even) td {
            background-color: #FFF0F5; /* Light purple */
        }
        
        
       
        
        
        
        
        .task-table tr.completed td {
            text-decoration: line-through;
            opacity: 0.6;
        }

        .circle-button {
            width: 40px; /* Adjust size */
            height: 40px; /* Adjust size */
            border-radius: 50%; /* Make it circular */
            background-color: #e0e0e0; /* Default color */
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 20px; /* Adjust the size of the checkmark */
        }

        .circle-button.completed {
            background-color: #A594F9; 
            color: white; /* Change text color to white */
        }

        .edit-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-circle {
            width: 100px;
            height: 100px;
            position: relative;
            margin: 20px auto; /* Add space above and below the circle */
        }

        .progress-circle svg {
            transform: rotate(-90deg);
        }

        .progress-circle circle {
            fill: none;
            stroke-width: 10;
        }

        .progress-circle .progress-bg {
            stroke: #e0e0e0;
        }

        .progress-circle .progress {
            stroke: #CDC1FF;
            transition: stroke-dashoffset 0.5s;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            font-weight: bold;
        }

        .date-row {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .date-cell {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 15px; /* More rounded corners */
            margin: 0 5px;
            text-align: center;
            width: 100px; /* Adjust width */
            background-color: #f5f5f5; /* Light background */
            transition: background-color 0.3s;
        }

        .date-cell.highlight {
            background-color: #A594F9; /* Highlight today */
            color: white;
        }

        .date-month {
            font-weight: 300; /* Lighter weight for month */
        }

        .date-day {
            font-weight: 700; /* Bolder weight for day */
            font-size: 22px; /* Bigger size for day */
        }

        .content-image {
            max-width: 100%; /* Responsive */
            height: auto; /* Maintain aspect ratio */
            float: right; /* Align to the right of the content */
            margin-left: 20px; /* Space between image and text */
        }

        .high-priority-tasks {
            background: #F5EFFF;
            padding: 15px;
            border-radius: 25px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            width: 90%;
            max-width: 600px;
            text-align: center;
        }

        .high-priority-tasks li {
            background: white;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 15px;
           
            display: flex;
            justify-content: space-between;
        }

        .high-priority-tasks.default-color li {
    border-color: #A594F9; /* Light gray for default */
}

.high-priority-tasks.blue-theme li, .sidebar.blue-theme, .sidebar.blue-theme a, .profile-container.blue-theme, .logout.blue-theme, .task-bar.blue-theme {
    border-color: #E8F9FF; /* Darker blue */
}


.high-priority-tasks.pink-theme li, .sidebar.pink-theme, .sidebar.pink-theme a, .profile-container.pink-theme, .logout.pink-theme,.task-bar.pink-theme {
    border-color: #ffdfea; /* Darker green */
}




.sidebar.blue-theme a:hover , .logout.blue-theme:hover {
    background: #F2FAFE;
}



.sidebar.pink-theme a:hover , .logout.pink-theme:hover {
    background: #FFDEDE;
}



.progress-circle.blue-theme .progress, .circle-button.blue-theme.completed{
    stroke: #9ACBD0; /* Blue theme progress color */
    background-color: #7AB2D3;
}


.progress-circle.pink-theme .progress, .circle-button.pink-theme.completed {
    stroke: #F7A8C4; 
    background-color: #F7A8C4;/* Green theme progress color */
}



      
        .theme-selector {
            position: relative; /* Required for the dropdown */
            margin-left: 10px; /* Space between home logo and theme selector */
        }

        .theme-icon {
    width: 40px; /* Size of the icon */
    height: 40px;
    cursor: pointer;
    transition: transform 0.2s; /* Add a hover effect */
    border: none; /* Remove border */
    background: none; /* Remove background */
    padding: 0; /* Remove padding */
}

.theme-icon:hover {
    transform: scale(1.1); /* Slightly enlarge on hover */
}

        .theme-dropdown {
            display: none; /* Hide dropdown by default */
            position: absolute;
            top: 40px; /* Adjust based on your layout */
            right: 0;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000; /* Ensure it appears above other elements */
        }

        .theme-dropdown button {
            width: 100%; /* Full-width buttons */
            padding: 10px;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
        }

        .theme-dropdown button:hover {
            background-color: #f0f0f0; /* Highlight on hover */
        }
        
        
        .date-cell.highlight.blue-theme, .task-bar.blue-theme button {
    background-color: #A3D4F7; /* Blue highlight */
}


.date-cell.highlight.pink-theme, .task-bar.pink-theme button {
    background-color: #FFC4C4; 
}




.task-bar.blue-theme{
    
   background-color: #F2FAFE; 
}

.task-bar.pink-theme{
    
   background-color: #FFF4F2; 
}



.icon-button {
    width: 40px; /* Size of the circular button */
    height: 40px;
    border-radius: 50%; /* Make it circular */
    background-color: #e0e0e0; /* Default color */
    border: none; /* Remove border */
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.3s;
    font-size: 16px; /* Adjust icon size */
}

.icon-button:hover {
    background-color: #d1d1d1; /* Darker on hover */
}

.edit-button,.delete-button, .save-button {
    background-color: #CDC1FF; /* Color for edit button */
}



.edit-button:hover, .delete-button:hover , .save-button {
    background-color: #8B7BFF; /* Darker for edit on hover */
}

.profile-container img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid grey;
}

.edit-button.blue-theme, .delete-button.blue-theme, .save-button.blue-theme {
            background-color: #7AB2D3; /* Blue theme color */
        }
        .edit-button.pink-theme, .delete-button.pink-theme, .save-button.pink-theme {
            background-color: #F7A8C4; /* Pink theme color */
        }
    </style>
</head>
<body>

<header class="<?= htmlspecialchars($colorTheme) ?>">
   <a href="MyTask.php"> <img src="images/logo.png" alt="Logo" class="logo"></a>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search tasks..." onclick="window.location.href='searchResults.php';">
    </div>
   
    
     <!-- Theme Selector Icon -->
    <div class="theme-selector">
        <button id="themeToggle" class="theme-icon">
            <img src="images/th.png" alt="Theme Selector" class="theme-icon" width="80px">
        </button>
        <div id="themeDropdown" class="theme-dropdown">
            <form method="POST">
                <button type="submit" name="color_theme" value="default-color"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACXElEQVR4nNWSzU8TQRjGe5FEL36csULa7ggoDeluW1GRlt2mmmpRaJdAd4kx6bl+HIzYetOL3gpalHZsEEP92mk1mBgSdk08EvxL7EVNifiambjbVlsD3HyTJ5l93uf97czs2mz/ZUlcsVvkcE5EeENCeCWEcGDIMR+XuMJ76tFewIkd24ThhITwdwlhMCVyhR9e+ywEnHnLo5kQKkz9EzaCCsMiwluNMFPDzidwoutRk8eyLjzUfncIr7SCMXEFEOzZVr13bYEiyn8OuBbYboKuBQZpHOY7s8wL/s7QrIjyG22BJ7vn10dceZCOYjjjeAy+I7Mw2JWjR2PydGbBZ59jvRDCEOTycKo7t94SpghkWhG0TVUgoPAaXOpfhgvHliDc8xSiAw8gOnAfzvYUmUd7NMOyglZT+LLSBJt2vzmg8uQbDVBFjz+3jjnln4NaeZRp0v/Q8mnGzCuC9jXpKe2vA73EbzapIn2L1uDVc4uwWbkItcoopMJ1/3zfMyuvCgQSHs1nARPeiruxOeZeZkOXTxNQvWVIiUtMdE092qOZxhnVR/otYNKT26MIpGodgddA5l9CKvyheUggzJP5F9YdquzIpEoZTfeo8uRe4+D1yCrcGluDK4NvLY+uqXcjstr0EkXQ7v71lZOe8j6VJ4YZmhnXISMbTBRCZT7fHtfrMJ7oMX9pb8tfJ9Zb6kjw2oQiaNcyslE1AX8qHTe+0AzNxnpLHbbtVDquF9sCZQPbdlp3Yp8OZeL664ys/6zvTN9Kx41XNyc/Htwx0KwZWT+cnliTqOh616Dd1i+nhuUhZVHVJgAAAABJRU5ErkJggg=="
 alt="purple Theme" class="theme-image" width="30px"></button>
                <button type="submit" name="color_theme" value="blue-theme"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACIklEQVR4nNWSy4oTQRSGs3FAN172LsS9LnUluvAVRFDxHXwIfQZ3kr2CpnMzQyZRugchjOM4pON0MqixK0knfak+pxIUxyNVSd+cROLsLPih6j9/fV2nqnO5/3K87AwuaV32VLPYbqFrlzVrcEv75tzRuqwiPVkrHYwurwXTDtj9gsVmWpdRpELX/lnxAip+GSWexWaFnn3vr7CCNbipWfZRGhap9NWhysTP+jL7id1YDZTtLYEp9RhVeXjct+zi6nYP2QfZVqnvUPHzUEHSmxWwx1RNZeQV9NjuSmDV9Xa2BFBDANUFUA2Ayo47B/cYvYa5V19kZLbiejtLYQbyhzrwHwaGpENI9eGEarZDNcelcgBU5kCbjqs8WZMZmTWAf9eRP8jAtnz/nA58qgIY0iYbx22+OhxS3psqyXnk19h4DkR1ANHyvLMx8K3g16OiVLWf/B7NcUB5f6bUcILkPvujOG9gSNuCX0uAiFfTxfrIVZsMx1frJsyl6o6najKTAQJciYEtolM6hkEcAE6NwYTeI2Q2SUmvMZyoTNwyhoFkZB8F+JP0xo+I1EakdylPzqW3h5j5iA788bFXbpF9xkD+JgqZKKizUHuh9DoB8qZBdHrpr7NPtKED3N0G/shEEUSAP2Wi8GVGZveJNnLrDBNEfhWwA+LZWpBc+rRBcMEEfGEC/opPBnjUQXy+FwTn/xkYDWs6vdgGuC0l5ycGnXT8Bjb6JBuG3FomAAAAAElFTkSuQmCC"
 alt="blue Theme" class="theme-image" width="30px"></button>
                
                <button type="submit" name="color_theme" value="pink-theme"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAACXBIWXMAAAsTAAALEwEAmpwYAAACR0lEQVR4nNWSy27TQBSGs6ESbLjsWSD2sIQVggWvgJAA8Q48BCyRnQUpEECFAnVKpUpAbk3tNFNIIZSktaLGjp3MOE5JUeMJDkUgykEzblyHplHbHUf6Jfs///k0t1Dov6wVMXtKE9GoLqCiJqK4LqBLZnThiibkElveqCag03uCaSK6rgnohy4i8CWg3w2pDEZkwfdYRhPQtaEwPZy7qIm5zT7YlswHH8AaV/s8lq3cnb8wbHXxQTCmahiBPbmyw9fE3JshK0QlI5IH8/5HMO7lOSQ4bE9WuMd6PBPJgx5GxV2B1kR5cS1eg6+JOrTemrA6rQMeK0E1PM9lT+mwOl3lPZZhWUsqLw6EtbP4pjNLflGFgJOpgy0tA3n6GeyXKjSlCldjQvU8aZlneFYmPzty/UYfzJFrx6iMN1iAqfGs6G/TCL+H9YcaF/vu+Y3xEs9SJhl/b6eNoz6wI1vn/aZCAD8q+INrsTK0ozqsR3Voxcq+jx9/2gYqBDqz1rnt7Wass8FmM+Y9jy8x1fNmsCeFQEvyeiwTnGnPWWd8IBQKh6hCaK/pyBiaL5bgW6bWN8TEvObzJZ4J+JQx+s9RIXeCg+5MHdy0yc4neFbcc9PehfgLUMjtHbdsF+wjVCZzvVA3VYNuyuRy0zWu3n83HVi5jLPkHTk88OmAqo5QmVx1FHyrmzSpD/hXSdNhGZYFVR0J7aXcpDG2G9BNGU9C+y2aICfclDHlJo0/AdBmN2m+oq/x8X0De7WR0E+6CeMyE/s+MOig9RdpUPgvv6LU5QAAAABJRU5ErkJggg=="
 alt="pink Theme" class="theme-image" width="30px"></button>
            </form>
        </div>
    </div>
</header>

<h1 style="text-align: center;">My Tasks</h1> <!-- Task title at the very top -->

<div class="container <?= htmlspecialchars($colorTheme) ?>">
    <div class="sidebar <?= htmlspecialchars($colorTheme) ?>">
        <div class="profile-container <?= htmlspecialchars($colorTheme) ?>">
            <img src="images/pro.png" alt="Profile">
            <span>Hello, <?= htmlspecialchars($username) ?></span>
        </div>
        <div>
            <a href="MyTask.php?view=all">
        <img src="<?= htmlspecialchars($currentThemeImages['tasks']) ?>" alt="My Tasks">
        My Tasks
    </a>
    <a href="ShoppingList.php">
        <img src="<?= htmlspecialchars($currentThemeImages['shopping']) ?>" alt="Shopping List">
        Shopping List
    </a>
</div>
<form method="POST" action="logOut.php" style="margin-top: 100px;">
    <button class="logout <?= htmlspecialchars($colorTheme) ?>">
        <img src="<?= htmlspecialchars($currentThemeImages['logout']) ?>" alt="Logout">
        Logout
    </button>
        </form>
    </div>

    <div class="content">
        <img class="themeImage" src="<?= htmlspecialchars($imageSrc) ?>" alt="flower"  class="content-image" style="width: 90px; height: auto; float: right; margin-left: 0px; margin-top:30px;">
        <img class="themeImage" src="<?= htmlspecialchars($imageSrc) ?>" alt="flower" class="content-image" style="width: 80px; height: auto; float: left; margin-left: 0px; margin-top:0px;">

        <h2><?= $showAll ? " My Tasks" : " High Priority Tasks" ?></h2>

        <?php if ($showAll): ?>
            <div class="progress-circle <?= htmlspecialchars($colorTheme) ?>">
                <svg width="100" height="100">
                    <circle class="progress-bg" cx="50" cy="50" r="45"></circle>
                    <circle class="progress" cx="50" cy="50" r="45" style="stroke-dasharray: 283; stroke-dashoffset: <?= 283 - (283 * $progressPercentage / 100) ?>;"></circle>
                </svg>
                <div class="progress-text"><?= $progressPercentageText ?>%</div>
            </div>

            <!-- Date Row -->
            <div class="date-row">
                <?php foreach ($dates as $date): ?>
                    <div class="date-cell <?= $date['isToday'] ? 'highlight ' . htmlspecialchars($colorTheme) : '' ?>">
                        <div class="date-month"><?= htmlspecialchars($date['month']) ?></div>
                        <div class="date-day"><?= htmlspecialchars($date['day']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$showAll): ?>
            <div class="high-priority-tasks <?= htmlspecialchars($colorTheme) ?>">
                <div id="searchLabel" style="display: none; font-weight: bold; color: #E0A800; margin-bottom: 10px; text-align:center;">
                    🔍 Showing search results...
                </div>
                <ul id="taskResults">
                    <?php foreach ($tasks as $task): ?>
                        <?php if ($task['Priority'] === 'High' && !$task['Completed']): ?>
                            <li>
                                <span><strong><?= htmlspecialchars($task['Task_Content']) ?></strong></span>
                                <span><?= $task['Due_Date'] ? date('Y-m-d', strtotime($task['Due_Date'])) : 'No Date' ?></span>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <table class="task-table <?= htmlspecialchars($colorTheme) ?>">
                <tbody id="taskResults">
                    <?php foreach ($tasks as $task): ?>
                        <tr class="<?= $task['Completed'] ? 'completed' : '' ?>">
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="task_id" value="<?= $task['TaskID'] ?>">
                                    <div class="circle-button <?= $task['Completed'] ? 'completed ' . htmlspecialchars($colorTheme) : '' ?>" onclick="this.parentNode.submit()">
    <?= $task['Completed'] ? '✔️' : '' ?>
</div>
                                    <input type="hidden" name="toggle_complete">
                                </form>
                            </td>

                            <?php if (isset($_POST['edit_task']) && $_POST['task_id'] == $task['TaskID']): ?>
                                <form method="POST" class="edit-form">
                                    <input type="hidden" name="task_id" value="<?= $task['TaskID'] ?>">
                                    <td><input type="text" name="updated_content" value="<?= htmlspecialchars($task['Task_Content']) ?>"></td>
                                    <td><select name="updated_priority">
                                        <option value="High" <?= $task['Priority'] === 'High' ? 'selected' : '' ?>>High</option>
                                        <option value="Medium" <?= $task['Priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="Low" <?= $task['Priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
                                    </select></td>
                                    <td><input type="date" name="updated_date" value="<?= $task['Due_Date'] ?>"></td>
                                    <td><button name="update_task" class="icon-button save-button <?= htmlspecialchars($colorTheme) ?>">💾</button></td>
                                </form>

                            <?php else: ?>
                                <td><?= htmlspecialchars($task['Task_Content']) ?></td>
                                <td><?= strtoupper($task['Priority']) ?></td>
                                <td><?= $task['Due_Date'] ? date('Y-m-d', strtotime($task['Due_Date'])) : 'No Date' ?></td>
                                <td>
                                    <form method="POST">
        <input type="hidden" name="task_id" value="<?= $task['TaskID'] ?>">
        <button name="edit_task" class="icon-button edit-button <?= htmlspecialchars($colorTheme) ?>">✏️</button>
    </form>
</td>
<td>
    <form method="POST" onsubmit="return confirmDelete();">
        <input type="hidden" name="task_id" value="<?= $task['TaskID'] ?>">
        <button name="delete_task" class="icon-button delete-button <?= htmlspecialchars($colorTheme) ?>">🗑️</button>
    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
                    <img class="themeImage" src="<?= htmlspecialchars($imageSrc) ?>" alt="flower"  class="content-image" style="width: 90px; height: auto; float: left; margin-right: 300px; margin-left: 0px; margin-top:30px;">

    </div>

    <?php if ($showAll): ?>
    <form class="task-bar <?= htmlspecialchars($colorTheme) ?>" method="POST" onsubmit="return validateForm()">
        <input type="text" name="task_content" id="task_content" placeholder="Enter task">
        <select name="priority" id="priority">
            <option value="" disabled selected>Priority</option>
            <option value="High">High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
        </select>
        <input type="date" name="due_date">
        <button name="add_task">+ Add a Task</button>
    </form>
    <?php endif; ?>

    <footer class="<?= htmlspecialchars($colorTheme) ?>">
        &copy; 2025 MONJEZ
    </footer>
</div>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this task?");
}


document.getElementById('themeToggle').addEventListener('click', function(event) {
    event.stopPropagation();  // Prevent the click from bubbling up to the window
    const dropdown = document.getElementById('themeDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
});

// Close dropdown if clicked outside
window.onclick = function(event) {
    const dropdown = document.getElementById('themeDropdown');
    if (!event.target.matches('#themeToggle') && !dropdown.contains(event.target)) {
        dropdown.style.display = "none";
    }
}

function validateForm() {
    const content = document.getElementById('task_content').value.trim();
    const priority = document.getElementById('priority').value;

    if (content === "") {
        alert("Please enter a task name.");
        return false; // Prevent form from submitting
    }

    if (priority === "") {
        alert("Please select a priority.");
        return false;
    }

    return true; // Allow form to submit
}
</script>

</body>
</html>  