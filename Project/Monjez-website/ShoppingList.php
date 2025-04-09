<?php
session_start();
include "db_connection.php";
$colorTheme = $_SESSION['color_theme'] ?? 'default-color';

// Check if a new color theme is set
if (isset($_POST['color_theme'])) {
    $colorTheme = $_POST['color_theme'];
    $_SESSION['color_theme'] = $colorTheme; // Store selected theme in session
}

if (!isset($_SESSION['user_id'])) {
    header("Location: logIn.php");
    exit();
}

$userID = $_SESSION['user_id'];

// جلب اسم المستخدم
$userQuery = $conn->prepare("SELECT Username FROM Users WHERE UserID = ?");
$userQuery->bind_param("i", $userID);
$userQuery->execute();
$userResult = $userQuery->get_result()->fetch_assoc();
$username = $userResult['Username'];

// إدارة الفئة المحددة
if (isset($_POST['select_category'])) {
    $_SESSION['selected_category'] = $_POST['select_category'];
} elseif (isset($_POST['back_to_categories'])) {
    unset($_SESSION['selected_category']);
}

// إضافة فئة
if (isset($_POST['add_category']) && !empty(trim($_POST['category_name']))) {
    $_SESSION['selected_category'] = trim($_POST['category_name']);
}

// إضافة عنصر
if (isset($_POST['add_item'])) {
    $item = $_POST['item_name'];
    $qty = $_POST['quantity'];
    $cat = $_SESSION['selected_category'];
    $stmt = $conn->prepare("INSERT INTO Shopping_List (UserID, Item_Name, Quantity, Category_Name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $userID, $item, $qty, $cat);
    $stmt->execute();
}

if (isset($_POST['delete_item'])) {
    $id = $_POST['item_id'];
    $stmt = $conn->prepare("DELETE FROM Shopping_List WHERE ShopListID = ? AND UserID = ?");
    $stmt->bind_param("ii", $id, $userID);
    $stmt->execute();

    echo "<script>window.location.href='ShoppingList.php';</script>";
    exit();
}

// تعديل عنصر
if (isset($_POST['update_item'])) {
    $id = $_POST['item_id'];
    $item = $_POST['item_name'];
    $qty = $_POST['quantity'];
    $stmt = $conn->prepare("UPDATE Shopping_List SET Item_Name = ?, Quantity = ? WHERE ShopListID = ? AND UserID = ?");
    $stmt->bind_param("siii", $item, $qty, $id, $userID);
    $stmt->execute();
}
if (isset($_POST['toggle_complete'])) {
    $id = $_POST['item_id'];
    $get = $conn->prepare("SELECT Purchased FROM Shopping_List WHERE ShopListID = ? AND UserID = ?");
    $get->bind_param("ii", $id, $userID);
    $get->execute();
    $res = $get->get_result()->fetch_assoc();
    $newVal = $res['Purchased'] ? 0 : 1;
    $stmt = $conn->prepare("UPDATE Shopping_List SET Purchased = ? WHERE ShopListID = ? AND UserID = ?");
    $stmt->bind_param("iii", $newVal, $id, $userID);
    $stmt->execute();
}


// جلب الفئات والعناصر
$cat_stmt = $conn->prepare("SELECT DISTINCT Category_Name FROM Shopping_List WHERE UserID = ?");
$cat_stmt->bind_param("i", $userID);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$categories = [];
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row['Category_Name'];
}

$items = [];
if (isset($_SESSION['selected_category'])) {
    $selected_category = $_SESSION['selected_category'];
    $item_stmt = $conn->prepare("SELECT * FROM Shopping_List WHERE UserID = ? AND Category_Name = ?");
    $item_stmt->bind_param("is", $userID, $selected_category);
    $item_stmt->execute();
    $items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
  <title>Shopping List</title>
  <link rel="stylesheet" href="style.css">
  
  <style>
      
    .main-content { flex: 1; margin-left: 290px; padding: 20px; text-align: center; }
    h1 {
        margin-top: 30px;
        font-size: 30px;
        text-align: center;
        font-weight: bold;
        color: #333;
        text-transform: uppercase;
        width: 100%;
    }

    .completed {
        
        text-decoration: line-through;
        opacity: 0.7;
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


    .bottom-panel {
        position: fixed;
        bottom: 50px; 
        left: 280px;
        width: calc(100% - 270px);
        background: #F5EFFF;
        padding: 15px;
        text-align: center;
        border-top: 2px solid #A594F9;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        box-shadow: 0px -2px 10px rgba(0, 0, 0, 0.1);
    }

    .add {
        padding: 8px; margin: 5px; border-radius: 25px; border: 1px solid #ddd; font-size: 14px;
        background: #A594F9; color: white; font-weight: bold; cursor: pointer;
    }

    input {
        padding: 8px; margin: 5px; border-radius: 25px; border: 1px solid #ddd; font-size: 14px;
    }

    .category-box { 
        background: white;
        padding: 20px;
        border-radius: 25px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        text-align: center;
        font-weight: bold;
        border: 3px solid #A594F9;
        width: 200px;
    }
    
    .category-box:hover { background: #F5EFFF; transform: scale(1.05); }
    
    .item-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #F5EFFF;
        padding: 15px;
        margin: 10px auto;
        border-radius: 15px;
        width: 60%;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    
    header.blue-theme, footer.blue-theme, .high-priority-tasks.blue-theme {
            background-color: #D6EEF9;
             border-left: #A594F9;

        }

        

        /* Green Theme */
         header.pink-theme, footer.pink-theme, .high-priority-tasks.pink-theme {
            background-color: #FFE3E1;
                        border-left:  #A594F9;

        }

        
        
        .sidebar.blue-theme, .sidebar.blue-theme a, .profile-container.blue-theme, .logout.blue-theme, .bottom-panel.blue-theme , .category-box.blue-theme {
    border-color: #E8F9FF; /* Darker blue */
}


.sidebar.pink-theme, .sidebar.pink-theme a, .profile-container.pink-theme, .logout.pink-theme, .bottom-panel.pink-theme , .category-box.pink-theme{
    border-color: #ffdfea; /* Darker green */
}

 

.sidebar.blue-theme a:hover , .logout.blue-theme:hover, .category-box.blue-theme:hover {
    background: #F2FAFE;
}



.sidebar.pink-theme a:hover , .logout.pink-theme:hover , .category-box.pink-theme:hover{
    background: #FFDEDE;
}




 .circle-button.blue-theme.completed{

    background-color: #7AB2D3;
}


 .circle-button.pink-theme.completed {

    background-color: #5A6C57;/* Green theme progress color */
}
 

.bottom-panel.blue-theme{
        background-color: #F2FAFE;/* Yellow theme progress color */

}

.bottom-panel.pink-theme{
        background-color: #FFF4F2;/* Yellow theme progress color */

}


.bottom-panel.blue-theme button{
    background-color: #A3D4F7; /* Blue highlight */
}


.bottom-panel.pink-theme button{
    background-color: #FFC4C4; /* Green highlight */

}




.item-box.blue-theme {
    background-color: #F2FAFE; /* Light blue */
}

.item-box.pink-theme {
    background-color: #FFF4F2; /* Light green */
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
    border: none;
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
   <a href="MyTask.php"><img src="images/logo.png" alt="Logo" class="logo"></a>
    
   
    
    
    <!-- Theme Selector Icon -->
    <div class="theme-selector">
        <button id="themeToggle" class="theme-icon">
            <img src="images/th.png" alt="Theme Selector" class="theme-icon">
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

  <div class="container <?= htmlspecialchars($colorTheme) ?>">
    <div class="sidebar <?= htmlspecialchars($colorTheme) ?>">
      <div class="profile-container <?= htmlspecialchars($colorTheme) ?>">
        <img src="images/pro2.png" alt="Profile Picture">
        <span>Hello, <?= htmlspecialchars($username) ?></span>
      </div>
      <div>
            <a href="MyTask.php"> 
<img src="<?= htmlspecialchars($currentThemeImages['tasks']) ?>" alt="My Tasks">
        My Tasks
    </a>
    <a href="ShoppingList.php">
        <img src="<?= htmlspecialchars($currentThemeImages['shopping']) ?>" alt="Shopping List">
        Shopping List
    </a>
</div>
      <form method="POST" action="logOut.php">
          <button class="logout <?= htmlspecialchars($colorTheme) ?>">
              
             <img src="<?= htmlspecialchars($currentThemeImages['logout']) ?>" alt="Logout">
        Logout
    </button>
      </form>
    </div>
    <div class="main-content">
        <img class="themeImage" src="<?= htmlspecialchars($imageSrc) ?>" alt="flower"  class="content-image" style="width: 90px; height: auto; float: right; margin-left: 0px; margin-top:30px;">
        <img class="themeImage" src="<?= htmlspecialchars($imageSrc) ?>" alt="flower" class="content-image" style="width: 87px; height: auto; float: left; margin-left: 0px; margin-top:0px;">

      <h1>My Shopping List</h1>

      <?php if (!isset($_SESSION['selected_category'])): ?>
        <form method="POST" class="bottom-panel <?= htmlspecialchars($colorTheme) ?>">
          <input type="text" name="category_name" placeholder="Enter Category">
          <button class="add" name="add_category">+Add a Category</button>
        </form>
      
        <div id="categoryContainer">
          <?php foreach ($categories as $cat): ?>
            <form method="POST" style="display:inline-block">
              <input type="hidden" name="select_category" value="<?= htmlspecialchars($cat) ?>">
              <button class="category-box <?= htmlspecialchars($colorTheme) ?>" type="submit"><?= htmlspecialchars($cat) ?></button>
            </form>
          <?php endforeach; ?>
            
        </div>
                    <img class="themeImage" src="<?= htmlspecialchars($imageSrc) ?>" alt="flower"  class="content-image" style="width: 90px; height: auto; float: left; margin-right: 300px; margin-left: 0px; margin-top:30px;">

      <?php else: ?>
        <form method="POST">
          <button class="back-button show" name="back_to_categories">⬅</button>
        </form>
        <form method="POST" class="bottom-panel <?= htmlspecialchars($colorTheme) ?>">
          <input type="text" name="item_name" placeholder="Item Name" required>
          <input type="number" name="quantity" placeholder="Quantity" required>
          <button class="add" name="add_item">Add Item</button>
        </form>

        <div id="itemList">
            
          <?php foreach ($items as $item): ?>
            <div class="item-box <?= $item['Purchased'] ? 'completed' : '' ?> <?= htmlspecialchars($colorTheme) ?>" id="item-<?= $item['ShopListID'] ?>">
              <form method="POST" style="margin-right: 10px;">
                <input type="hidden" name="item_id" value="<?= $item['ShopListID'] ?>">
                <input type="hidden" name="toggle_complete" value="1">
                <div class="circle-button <?= $item['Purchased'] ? 'completed ' . htmlspecialchars($colorTheme) : htmlspecialchars($colorTheme) ?>" onclick="this.parentNode.submit()">
    <?= $item['Purchased'] ? '✔️' : '' ?>
</div>
                
              </form>
                
                
              <form method="POST" id="form-<?= $item['ShopListID'] ?>" style="display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 10px;">
                <input type="hidden" name="item_id" value="<?= $item['ShopListID'] ?>">
                <span id="name-text-<?= $item['ShopListID'] ?>"><?= htmlspecialchars($item['Item_Name']) ?></span>
                <input type="text" name="item_name" id="name-input-<?= $item['ShopListID'] ?>" value="<?= htmlspecialchars($item['Item_Name']) ?>" style="display:none;">
                <span id="qty-text-<?= $item['ShopListID'] ?>"> <?= $item['Quantity'] ?></span>
                <div class="quantity-container" id="qty-edit-<?= $item['ShopListID'] ?>" style="display:none;">
                  <button type="button" onclick="changeQty(this, -1)">➖</button>
                  <input type="number" name="quantity" value="<?= $item['Quantity'] ?>" min="1" style="width: 50px; text-align:center;">
                  <button type="button" onclick="changeQty(this, 1)">➕</button>
                </div>
                
                <div class="buttons-container">
                  <button type="button" class="icon-button edit-button <?= htmlspecialchars($colorTheme) ?>" onclick="enableEdit(<?= $item['ShopListID'] ?>)">✏️</button>
                  <button name="update_item" class="icon-button save-button <?= htmlspecialchars($colorTheme) ?>" id="save-btn-<?= $item['ShopListID'] ?>" style="display:none;">💾</button>
                  <button name="delete_item" class="icon-button delete-button <?= htmlspecialchars($colorTheme) ?>">🗑️</button>
                </div>
              </form>
            </div>
            
          <?php endforeach; ?>
                    <img class="themeImage" src="<?= htmlspecialchars($imageSrc) ?>" alt="flower"  class="content-image" style="width: 90px; height: auto; float: left; margin-right: 300px; margin-left: 0px; margin-top:30px;">

        </div>
      <?php endif; ?>
    </div>
  </div>
  <footer class="<?= htmlspecialchars($colorTheme) ?>">
    &copy; 2025 MONJEZ
  </footer>

  <script>
    function enableEdit(id) {
      document.getElementById('name-text-' + id).style.display = 'none';
      document.getElementById('qty-text-' + id).style.display = 'none';
      document.getElementById('name-input-' + id).style.display = 'inline-block';
      document.getElementById('qty-edit-' + id).style.display = 'flex';
      document.querySelector(`#form-${id} button[onclick*="enableEdit"]`).style.display = 'none';
      document.getElementById('save-btn-' + id).style.display = 'inline-block';
    }

    function changeQty(btn, delta) {
      const input = btn.parentElement.querySelector('input[type="number"]');
      let current = parseInt(input.value);
      current += delta;
      if (current < 1) current = 1;
      input.value = current;
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

// Change image based on theme selection
const changeImageOnThemeChange = (theme) => {
    const themeImage = document.getElementById('themeImage');
    if (theme === 'blue-theme') {
        themeImage.src = 'images/2.png'; // Change to image 2 for blue theme
    } else if (theme === 'pink-theme') {
        themeImage.src = 'images/3.png'; // Change to image 3 for pink theme
    } else {
        themeImage.src = 'images/1.png'; // Default image
    }
};

// Add event listeners to theme buttons
document.querySelectorAll('.theme-dropdown button').forEach(button => {
    button.addEventListener('click', function() {
        changeImageOnThemeChange(this.value);
    });
});
  </script>
</body>
</html>

<?php
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>