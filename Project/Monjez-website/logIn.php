<?php
session_start();
$colorTheme = isset($_COOKIE['color_theme']) ? $_COOKIE['color_theme'] : 'default-color';
include 'db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];  
    $password = $_POST['password'];

    $query = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);  
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];

            if (isset($_COOKIE['color_theme'])) {
                $_SESSION['color_theme'] = $_COOKIE['color_theme'];
            } 
            
            header("Location: MyTask.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Incorrect username or password!";
            header("Location: logIn.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Incorrect username or password!";
        header("Location: logIn.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monjez - Login</title>
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
        .signup-box {
            padding: 10px 20px;
    background: #A594F9;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            margin-right: 20px;
        }
        .signup-box:hover {
            background: #CDC1FF;
        }
        .container {
            width: 70%;
            max-width: 900px;
            background: #F5EFFF;
            border-radius: 12px;
            display: flex;
            overflow: hidden;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .left {
            flex: 1;
            background: linear-gradient(135deg,#A594F9, #E5D9F2);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .left h1 {
            margin: 0;
            font-size: 37px;
            font-weight: bold;
        }
        .left p {
            text-align: center;
            margin-top: 10px;
            font-size: 16px;
        }
        .right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
            border-left: 2px solid #A594F9;
        }
        .right h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #6D4C41;
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

        .left ,  .btn,   .btn:hover{
            background: linear-gradient(135deg,#D6EEF9, #F2FAFE);
        }
  
        .input-group input, .right{
             border: 3px solid #D6EEF9;
        }
        
        .signup-box{
            background: #D6EEF9;
        }
        
        .signup-box:hover{
            background: #F2FAFE;
        }
        <?php elseif ($colorTheme === 'pink-theme'): ?>

         header, footer {
            background-color: #FFE3E1;
                 border-color: #ffdfea;     
        }
        
        .left ,  .btn,   .btn:hover{
            background: linear-gradient(135deg,#F7CFD8, #FFF0F5);
        }

        .input-group input, .right{
             border: 3px solid #FFF4F2;
        }
        
        .signup-box{
            background: #F7CFD8;
        }
        
        .signup-box:hover{
            background: #FFDEDE;
        }
        <?php endif; ?>
    </style>
</head>

<body>
    <header>
        <img src="images/logo.png" alt="Logo" class="logo">
        <a href="SignUp.php" class="signup-box">Sign Up</a>
        
    </header>
    
    <div class="container">
        <div class="left">
            <h1>Welcome to Monjez</h1>
            <p>Get your tasks done quickly and efficiently with Monjez, the ultimate productivity app!</p>
        </div>
        <div class="right">
            
           
            <?php if (isset($_SESSION['error_message'])): ?>
                <p style="color: red; text-align: center;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            <?php endif; ?>

            <form action="logIn.php" method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button class="btn" type="submit">Login</button>
            </form>
        </div>
    </div>
    
    <footer>
        &copy; 2025 MONJEZ
    </footer>
</body>
</html>