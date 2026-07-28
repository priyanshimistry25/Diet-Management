<?php
session_start();
include("connection.php");

if(!isset($_SESSION["user_id"])){
    header("location:login.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $goal = $_POST["goal"];
    $user_id = $_SESSION["user_id"];

    if(empty($goal)){
        $error = "Please select a goal.";
    } else {

        $stmt = $conn->prepare("UPDATE client,user SET goal=?, is_profile_complete=1 WHERE client.user_id=?");
        $stmt->bind_param("ss", $goal, $user_id);
        $stmt->execute();

        header("location:client/dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Goal</title>
    <link rel="stylesheet" href="goal.css">
</head>
<body>

<div class="container">
    <h2>Select Your Goal</h2>

    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">

        <label class="card">
            <input type="radio" name="goal" value="weight_loss">
            <span>🔥 Weight Loss</span>
        </label>

        <label class="card">
            <input type="radio" name="goal" value="weight_gain">
            <span>💪 Weight Gain</span>
        </label>

        <label class="card">
            <input type="radio" name="goal" value="maintain">
            <span>⚖️ Maintain Weight</span>
        </label>

        <label class="card">
            <input type="radio" name="goal" value="dietitian">
            <span>👨‍⚕️ Follow Dietitian</span>
        </label>

        <button type="submit">Continue</button>
    </form>
</div>

</body>
</html>

