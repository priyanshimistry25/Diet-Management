<?php
    session_start();
    include("../connection.php");

    if(!isset($_SESSION["user_id"])){
        header("Location: ../login.php");
        exit();
    }
    include("header.php");

    $user_id = $_SESSION["user_id"];

    $stmt = $conn->prepare("SELECT * FROM client WHERE user_id=?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $goal = $data["goal"];
    $tdee = $data["tdee"];
    $caloriesBurned=0;
    $netCalories=0;
    $caloriesEaten=0;

    if($goal == "weight_loss"){
        $targetCalories = $tdee - 500;
    }
    elseif($goal == "weight_gain"){
        $targetCalories = $tdee + 500;
    }
    else{
        $targetCalories = $tdee;
    }

    $today = date("Y-m-d");

    // total eaten
    $mealQuery = $conn->prepare("SELECT calories as total FROM meals WHERE user_id=? AND date=?");
    $mealQuery->bind_param("ss", $user_id, $today);
    $mealQuery->execute();
    $mealData = $mealQuery->get_result()->fetch_assoc();
    $caloriesEaten = $mealData['total'] ?? 0;

    // total burned
    $exerciseQuery = $conn->prepare("SELECT calories_burned as total FROM exercise WHERE user_id=? AND date=?");
    $exerciseQuery->bind_param("ss", $user_id, $today);
    $exerciseQuery->execute();
    $exerciseData = $exerciseQuery->get_result()->fetch_assoc();
    $caloriesBurned = $exerciseData['total'] ?? 0;

    // net calories
    $netCalories = $caloriesEaten - $caloriesBurned;

    echo "<h3>Target Calories: $targetCalories</h3>";
    echo "<h3>Consumed: $caloriesEaten</h3>";
    echo "<h3>Burned: $caloriesBurned</h3>";
    echo "<h3>Net: $netCalories</h3>";

    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div id="container">
        <form action="newExercise.php" method="post">
            <div class="field">
                <button><i class="fas fa-dumbbell exercise"></i></button>
                <p>Exercise Log</p>
            </div>
        </form>
        
        <form action="linkFitnessDevice.php" method="post">
        <div class="field">
           <button><i class="fas fa-link device"></i></button>
            <p>Link Fitness Device</p>
        </div>
        </form>

        <form action="Barcode scan.php" method="post">
        <div class="field">
            <button><i class="fas fa-barcode barcode"></i></button>
            <p>Barcode Scanning</p>
        </div>
        </form>

        <form action="newLogmeal.php" method="post">
        <div class="field">
            <button><i class="fas fa-utensils meal"></i></button>
            <p>Log Meal</p>
        </div>
        </form>

        <form action="recipes.php" method="post">
        <div class="field">
            <button><i class="fas fa-search recipe"></i></button>
            <p>Recipe Search</p>
        </div>
        </form>

        <form action="reminders.php" method="post">
        <div class="field">
            <button><i class="fas fa-bell reminder"></i></button>
            <p>Reminders</p>
        </div>
        </form>

    </div>

        
</body>
</html>