<?php

include("connection.php");
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
    
</head>
<body>
    <form action="verifyLogin.php" method="post">

    <div id="container">
        
        <div>
           <h1> Log In </h1><br>
           <?php
                if (isset($_GET['errorMsg'])) {
                $errorMsg = htmlspecialchars($_GET["errorMsg"]);
                echo "<p style ='color:red'>".$errorMsg."</p>";
                }
           ?>
           <div class="form_field">
                <label for="role">Role:</label><br>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Choose role</option>
                    <option value="client">Client</option>
                    <option value="dietitian">Dietitian</option>
                </select>
            </div>
            <div class="form_field">
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" required>
                
            </div>
            <div class="form_field">
                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form_field" id="sbt">
                <input type="submit" name="submit" id="btn">

            </div>
            <div class="form_field">
                <a href="passwordReset.php" style="color:purple;" >Forgot password?</a>&nbsp;&nbsp;&nbsp;
                <a href="registration.php" style="color:purple;" >register if you haven't</a>
            </div>
            
        </div>

    </div>

    </form>
    
</body>
</html>