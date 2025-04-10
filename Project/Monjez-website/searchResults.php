<?php
session_start();
include 'db_connection.php';

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
    <title>Search Results - Monjez</title>
    <link rel="stylesheet" href="style.css">
      <style>

        header.blue-theme, footer.blue-theme {
            background-color: #D6EEF9;
             border-left: #A594F9;

        }

       

 
         header.pink-theme, footer.pink-theme {
            background-color: #FFE3E1;
                        border-left:  #A594F9;

        }
        
.sidebar.blue-theme, .sidebar.blue-theme a, .profile-container.blue-theme, .logout.blue-theme {
    border-color: #E8F9FF; 
}


.sidebar.pink-theme, .sidebar.pink-theme a, .profile-container.pink-theme, .logout.pink-theme{
    border-color: #ffdfea; 
}

 

.sidebar.blue-theme a:hover , .logout.blue-theme:hover{
    background: #F2FAFE;
}



.sidebar.pink-theme a:hover , .logout.pink-theme:hover {
    background: #FFDEDE;
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
        
        .back-to-tasks.blue-theme {
    background: #7AB2D3;
    
}

.back-to-tasks.blue-theme:hover {
    background: #F2FAFE;
}

 .back-to-tasks.pink-theme {
    background: #FFC4C4;
    
}

.back-to-tasks.pink-theme:hover {
    background: #F7A8C4;
}

 .back-to-tasks.default-color{
    background: #A594F9;
    
}

.back-to-tasks.default-color:hover {
    background: #E5D9F2;
}

.profile-container img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid grey;
}

          </style>
</head>
<body>

<header class="<?= htmlspecialchars($colorTheme) ?>">
   <a href="MyTask.php"><img src="images/logo.png" alt="Logo" class="logo"></a>
    
    
    <div class="search-box">
        <form method="GET" action="searchResults.php" onsubmit="return validateSearchForm()">
            <input type="text" id="searchInput" name="search" placeholder="Search tasks..." 
                   value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" style="display:none;">Search</button>
            
            
        </form>
    </div>
   
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
            <img src="images/pro.png" alt="Profile">
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

                <a href="MyTask.php?view=all" class="back-to-tasks <?= htmlspecialchars($colorTheme) ?>">Back to Tasks</a>
            </div>
        </div>
    </div>
</div>

    <footer class="<?= htmlspecialchars($colorTheme) ?>">
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
</script>

</body>
</html>
