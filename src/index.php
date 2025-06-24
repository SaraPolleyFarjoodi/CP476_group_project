<?php 
session_start(); //start the PHP session in order for $_SESSION to work (to store and access the login credentails across multiple pages)
$error = '';  //initialize the error message variable

if($_SERVER["REQUEST_METHOD"] == "POST") { //check if the form is submitted (the user clicked the Login button)
    //get the username and password from the form
    $user = $_POST['username'];
    $pass = $_POST['password'];

    //check if the username and password are correct and try to connect to the MySQL database
    try {
        $conn = new PDO('mysql:host=db;dbname=myapp;charset=utf8', $user, $pass); //connect to database called myapp
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //if an error occurs while using the connection, throw an exception

        //save credentials in session using super global variable
        $_SESSION['db_user'] = $user;
        $_SESSION['db_pass'] = $pass;
        header("Location: userdashboard.php"); //send HTTP header to redirect the user to the userdashboard.php page
        exit();
    } catch (PDOException $e) {
        $error = "Incorrect username or password. ";
        $error .= "You entered Username: " . htmlspecialchars($user) . " Password: " . htmlspecialchars($pass);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
</head>
<body>
    <h2>Login</h2>
    <?php if($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error)?></p>
    <?php endif; ?>
    <form method="post" action="index.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" value="Login">	
    </form>
</body>
</html>
