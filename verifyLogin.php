<?php
    session_start();
    include("connection.php");
    if(isset($_POST["submit"])){

        $role = $_POST["role"];
        $email = $_POST["email"];
        $password = $_POST["password"];

        if(empty($email) || empty($password)){
        header("location:login.php?errorMsg=All fields are required.");
        exit();
        }

        $stmt = $conn->prepare("SELECT * FROM user WHERE role=? AND email=?");
        $stmt->bind_param("ss",$role,$email);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

       if($result->num_rows===1){

            if(password_verify($password,$data["password"])){
                if($data["role"] === "client"){
                    $_SESSION["user_id"] = $data["user_id"];
                    if($data['is_profile_complete'] == 0){
                        header("location:goal_selection.php");
                        exit();
                    }else{
                        header("location:client/dashboard.php");
                        exit();
                    }
                    
                }
                else if($data["role"] === "dietitian"){
                    $_SESSION["user_id"] = $data["user_id"];
                    header("location:dietitian/dashboard.php");
                    exit();
                }
            }
            else {
                header("location:login.php?errorMsg=Incorrect Password.");
                exit();
            }

       }else {
            header("location:login.php?errorMsg=Invalid email or Role.");
            exit();
        } 

    }else{
        header("location:login.php");
        exit();
    }
    
?>