<?php
session_start();
$colorTheme = isset($_COOKIE['color_theme']) ? $_COOKIE['color_theme'] : 'default-color';

include 'db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail = trim($_POST['email']);
    $userUsername = trim($_POST['username']);
    $userPassword = trim($_POST['password']);
    $userPhone = trim($_POST['phone']);


  if (empty($userEmail) || empty($userUsername) || empty($userPassword) || empty($userPhone)) {
   die("<script>alert('All fields are required. Please fill them out.'); window.history.back();</script>");
    }
    if (strlen($userPassword) < 8) {
        die("<script>alert('Password must be at least 8 characters long.'); window.history.back();</script>");
    }
    if (!preg_match("/^[0-9]+$/", $userPhone)) {
        die("<script>alert('Phone number should only contain digits.'); window.history.back();</script>");
    }
    if (strlen($userPhone) != 10) {
    die("<script>alert('Phone number must be  10 digits long.'); window.history.back();</script>");
}


    $checkEmailSql = "SELECT * FROM Users WHERE Email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("s", $userEmail);
    $checkEmailStmt->execute();
    $emailResult = $checkEmailStmt->get_result();

    if ($emailResult->num_rows > 0) {
        die("<script>alert('This email is already in use. Please provide a different one.'); window.history.back();</script>");
    }
    $checkEmailStmt->close();

    $checkUsernameSql = "SELECT * FROM Users WHERE Username = ?";
    $checkUsernameStmt = $conn->prepare($checkUsernameSql);
    $checkUsernameStmt->bind_param("s", $userUsername);
    $checkUsernameStmt->execute();
    $usernameResult = $checkUsernameStmt->get_result();

    if ($usernameResult->num_rows > 0) {
        die("<script>alert('This username is already taken. Please choose a different one.'); window.history.back();</script>");
    }
    $checkUsernameStmt->close();

    $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

    $sql = "INSERT INTO Users (Email, Username, Password, Phone_Number) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $userEmail, $userUsername, $hashedPassword, $userPhone);

  if ($stmt->execute()) {
    $newUserID = $stmt->insert_id;

  
    $_SESSION['user_id'] = $newUserID;
    $_SESSION['username'] = $userUsername;

    if (isset($_COOKIE['color_theme'])) {
                $_SESSION['color_theme'] = $_COOKIE['color_theme'];
            }
            
    header("Location: MyTask.php");
    exit;


        
        
        
    } else {
        echo "<script>alert('An error occurred during registration. Please try again later.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monjez - Sign Up</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding-top: 80px; 
            padding-bottom: 80px; 
        }

        header { 
          
            
          
    background: #E5D9F2;
    color: black;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    height: 60px;
    z-index: 1000;
                border-bottom: 3px solid #A594F9;


        }

        footer { 
            
            
            background: #E5D9F2;
    color: black;
    text-align: center;
    padding: 15px;
    position: fixed;
    bottom: 0;
    width: 100%;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
                border-top: 3px solid #A594F9;

        }
        
        
        .logo {
            height: 70px;
            width: auto;
        }
        .login-box {
            padding: 10px 20px;
            background: #A594F9;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            margin-right: 20px;
        }
        .login-box:hover {
            background: #CDC1FF;
        }
        .container {
            width: 70%;
            max-width: 900px;
            background: white;
            border-radius: 12px;
            display: flex;
            overflow: hidden;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
            border-left: 3px solid #A594F9;
        }
        .right h2 {
            text-align: center;
            margin-bottom: 25px;
            color: white;
            font-size: 24px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 3px solid #A594F9;
            border-radius: 25px;
            background: white;
            font-size: 16px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg,#A594F9, #E5D9F2);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }
        .btn:hover {
            background: linear-gradient(135deg,#A594F9, #E5D9F2);
        }
        
        <?php if ($colorTheme === 'blue-theme'): ?>
         header, footer {
            background-color: #D6EEF9;
            border-color: #E8F9FF;

        }

        .btn,   .btn:hover{
            background: linear-gradient(135deg,#D6EEF9, #F2FAFE);
        }
  
        .input-group input, .right{
             border: 3px solid #D6EEF9;
        }
        
        .login-box{
            background: #D6EEF9;
        }
        
        .login-box:hover{
            background: #F2FAFE;
        }
        <?php elseif ($colorTheme === 'pink-theme'): ?>

         header, footer {
            background-color: #FFE3E1;
                 border-color: #ffdfea;     
        }
        
         .btn,   .btn:hover{
            background: linear-gradient(135deg,#F7CFD8, #FFF0F5);
        }

        .input-group input, .right{
             border: 3px solid #FFF4F2;
        }
        
        .login-box{
            background: #F7CFD8;
        }
        
        .login-box:hover{
            background: #FFDEDE;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <header>
        <img src="images/logo.png" alt="Logo" class="logo">
        <a href="logIn.php" class="login-box">Login</a>
        
    </header>
    <div class="container">
        <div class="right">
            <form action="signup.php" method="POST">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-group">
                    <input type="tel" name="phone" placeholder="Phone Number" required>
                </div>
                <button type="submit" class="btn">Sign Up</button>
            </form>
        </div>
    </div>
    <footer>
        &copy; 2025 MONJEZ
    </footer>
</body>
</html>