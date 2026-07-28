<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include("connection.php");
session_start();
include("header.php");


    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $gender = $_POST["gender"];
    $age = null;
    $password = $_POST["password"];
    
    $nameParts = explode(" ",$fullname);
    $firstName = $nameParts[0];
    $datetime = date("YmdHis");
    
    $userId = $firstName . $datetime;
    $username = $email;

    // $passwordLength = 12;
    // $symbolsAllow = true;
    // $numbersAllow = true;
    // $uppercaseAllow = true;
    // $lowercaseAllow = true;
    
    // function generatePassword($passwordLength,$symbolsAllow,$numbersAllow,$uppercaseAllow,$lowercaseAllow){

    //     $lowercase  = "abcdefghijklmnopqrstuvwxyz";
    //     $uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    //     $numbers = "0123456789";
    //     $symbols = "^*_+%$~/";

    //     $allowedChars = "";
    //     $password = "";

    //     $allowedChars .= $symbolsAllow ? $symbols : "";
    //     $allowedChars .= $numbersAllow ? $numbers : "";
    //     $allowedChars .= $uppercaseAllow ? $uppercase : "";
    //     $allowedChars .= $lowercaseAllow ? $lowercase : "";

    //     if($passwordLength < 8){
    //         return "password length must be atleast 8";
    //     }

    //     if(strlen($allowedChars)== 0){
    //         return "allow atleast 1 set of characters";
    //     }

    //     for ($i = 0; $i<$passwordLength; $i++) {

    //         $index = random_int(0, strlen($allowedChars) - 1);
    //         $password .= $allowedChars[$index];

    //     }

    //     return $password;
    // }
    
    
    // $password = generatePassword($passwordLength,$symbolsAllow,$numbersAllow,$uppercaseAllow,$lowercaseAllow);
    $passHash = password_hash($password,PASSWORD_DEFAULT);
    $success = true; //registration success
    

    if($role ==="client"){
        $age = $_POST["ageC"];
        $heightCM = $_POST["height"];
        $weightKG = $_POST["weight"];
        $activeness = $_POST["activeness"];
        $BMI =  $weightKG/(($heightCM/100)**2);
        
        if($gender == "female"){
            $BMR = ($weightKG*10)+($heightCM*6.25)-($age*5)-161;
        }
        else if($gender == "male"){
            $BMR = ($weightKG*10)+($heightCM*6.25)-($age*5)+5;
        }
        
        switch($activeness){
            case "lessActive": $TDEE = $BMR*1.2;
                break;
            case "moderatelyActive": $TDEE = $BMR*1.55;
                break;
            case "highlyActive": $TDEE = $BMR*1.725;
                break;
            default: $TDEE =0;
        }

        try{

            $stmt = $conn->prepare("INSERT INTO client (age,user_id,height,weight,physical_activeness,gender,bmi,bmr,tdee) VALUES(?,?,?,?,?,?,?,?,?)");
            $stmt ->bind_param("isddssddd",$age,$userId,$heightCM,$weightKG,$activeness,$gender,$BMI,$BMR,$TDEE);
            $stmt ->execute();
        
        }catch (mysqli_sql_exception $e) {
            $success = false; 
            if ($e->getCode() == 1062) {
                header("Location: registration.php?error=user_exists");
                exit;// duplicate entry
            } else {
                echo "Error: " . $e->getMessage();
            }
        }   
            
            
    }
    else if($role ==="dietitian"){
        $age = $_POST["ageD"];
        $address = $_POST["address"];
        $contactNumber = $_POST["contactNumber"];
        if(isset($_POST["license"])){
            $license = "true";
        }
        else{
            $license = "false";
        }

        try{

            $stmt = $conn->prepare("INSERT INTO dietitian (age,user_id,gender,address,contact_number,license) VALUES(?,?,?,?,?,?)");//include username column as well
            $stmt ->bind_param("isssss",$age,$userId,$gender,$address,$contactNumber,$license);
            $stmt ->execute();
        
        }catch (mysqli_sql_exception $e) {
            $success = false; 
            if ($e->getCode() == 1062) {
                header("Location: registration.php?error=user_exists");
                exit; // duplicate entry
            } else {
                echo "Error: " . $e->getMessage();
            }
        }
        
    }
    if ($success) {
        try{

            $stmt = $conn->prepare("INSERT INTO user (full_name,email,role,age,user_id,password) VALUES(?,?,?,?,?,?)");
            $stmt ->bind_param("sssiss",$fullname,$email,$role,$age,$userId,$passHash);
            $stmt ->execute();

        }catch (mysqli_sql_exception $e) {
            $success = false; 
            if ($e->getCode() == 1062) {
                header("Location: registration.php?error=user_exists");
                exit; // duplicate entry
            } else {
                echo "Error: " . $e->getMessage();
            }
        }
    }
    
        if($success){
            header("Location: login.php");
            session_destroy();
            exit;
        

        }
        
    } 

    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration_Varify</title>
    <style>
        body{
            background-color: lavenderblush;
        }
    </style>
</head>
<body>
    
</body>
</html>