<?php
include("connection.php");
session_start();

// If already logged in, redirect to dashboard
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="registration.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <form action="verifyRegistration.php" method="post">
   
    <div id="container">
        <?php
            if (isset($_GET['error'])) {
                if ($_GET['error'] === "user_exists") {
                    echo "<p style='color:red;'>User already exists. Try another email.</p>";
                }
            }
        ?> 
        <div id="generalForm">
            <h1>Registration</h1>
            <div class="formfield">
                <label for="fullname" >Full Name:</label>
                <input type="text" name="fullname" id="fullname" required><br>
            </div>
            <small class="d-none" id="fullnameError"></small>
            <div class="formfield">
                <label for="email" >Email:</label>
                <input type="email" name="email" id="email" required><br>
            </div>
            <small class="d-none" id="emailError"></small>
            <div class="formfield">
                <label for="password" >Password:</label>
                <input type="password" name="password" id="password" required><br>
            </div>
            <small class="d-none" id="passwordError" style="color:red; "></small>
            <div class="formfield">
                <label for="gender" >Gender:</label>
                <input type="radio" name="gender" value="male" required>Male<br>
                <input type="radio" name="gender" value="female" required>Female<br>
            </div>
            <div class="formfield">
                <label for="role" >Role:</label>
                <select name="role" id="role" onchange="showform()" required>
                    <option value="" disabled selected>Choose Role</option>
                    <option value="client" >Client</option>
                    <option value="dietitian" >Dietitian</option>
                </select><br>
            </div>
        </div>
        <div id="clientForm">
            <div class="formfield">
                <label for="ageC" >Age:</label>
                <input type="number" name="ageC" required><br>
            </div>
            <div class="formfield">
                <label for="height" >Height (cm):</label>
                <input type="number" name="height" required><br>
            </div>
            <div class="formfield">
                <label for="weight" >Weight (kg):</label>
                <input type="number" name="weight" required><br>
            </div>
            <div class="formfield">
                <label for="activeness" >Physical Activeness:</label>
                <select name="activeness" required>
                    <option value=""  disabled selected>Choose daily activeness</option>
                    <option value="lessActive" >Less Active</option>
                    <option value="moderatelyActive" >Moderately Active</option>
                    <option value="highlyActive" >Highly Active</option>
                </select>
            </div>
        </div>
        <div id="dietitianForm">
            <div class="formfield">
                <label for="ageD" >Age:</label>
                <input type="number" name="ageD" required><br>
            </div>
            <div class="formfield">
                <label for="address" id="addLab" >Address:</label>
                <textarea id="address" name="address" rows="4"  required></textarea><br>
            </div>
            <div class="formfield">
                <label for="contactNumber" >Contact No:</label>
                <input type="text" name="contactNumber" id="contactNumber" required><br>
            </div>
            <small class="d-none" id="contactNumberError"></small>
            <div class="formfield">
                <label for="license" >License:</label>
                <input type="file" name="license" required><br>
            </div>
        </div>
        <div class="formfield" id="sbt">
            <input type="submit" name="submit" id="btn">
        </div>
    </div>
    </form>
    <script>
        // function showform(){
        //     let role = document.getElementById("role").value;
            
        //     if(role==="client"){
        //         let clientform = document.getElementById("clientForm");
        //         clientform.style.display = "block";
        //         let dietitianform = document.getElementById("dietitianForm");
        //         dietitianform.style.display = "none";

        //     }
        //     else if(role==="dietitian"){
        //         let dietitianform = document.getElementById("dietitianForm");
        //         dietitianform.style.display = "block";
        //         let clientform = document.getElementById("clientForm");
        //         clientform.style.display = "none";
        //     }
        // }


        function showform() {
            let role = document.getElementById("role").value;

            let clientform = document.getElementById("clientForm");
            let dietitianform = document.getElementById("dietitianForm");

            let clientInputs = clientform.querySelectorAll("input, select");
            let dietitianInputs = dietitianform.querySelectorAll("input, textarea");

            if (role === "client") {
                clientform.style.display = "block";
                dietitianform.style.display = "none";

                clientInputs.forEach(el => el.required = true);
                dietitianInputs.forEach(el => el.required = false);

            } else if (role === "dietitian") {
                dietitianform.style.display = "block";
                clientform.style.display = "none";

                dietitianInputs.forEach(el => el.required = true);
                clientInputs.forEach(el => el.required = false);

            } else {
                clientform.style.display = "none";
                dietitianform.style.display = "none";

                clientInputs.forEach(el => el.required = false);
                dietitianInputs.forEach(el => el.required = false);
            }
        }
        
        window.history.replaceState(null, '', window.location.pathname);

        let fullnameValid = false;
        let emailValid = false;
        let passwordValid = false;
        let contactNumberValid = false;

        $("#email").on("input",function(){
            let emailText = $(this).val().toLowerCase();
            $(this).val(emailText);
        });

        $("#email").on("input",function(){

            const email = $(this).val().trim();

            const basicPattern = /^[a-zA-Z0-9]+([._%+-][a-zA-Z0-9]+)*@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if(email.length === 0){
                $("#emailError").text("Email address is required.").css("color","red").removeClass("d-none");
            }else if(!email.includes("@")){
                $("#emailError").text("Email must contain '@' symbol.").css("color","red").removeClass("d-none");
            }else if(!email.includes(".")){
                $("#emailError").text("Email must contain a domain extension (e.g. .com)").css("color","red").removeClass("d-none");
            }else if(!basicPattern.test(email)){
                $("#emailError").text("Please enter a valid email address.").css("color","red").removeClass("d-none");
            }else{
                $("#emailError").addClass("d-none");
                emailValid = true;
                return;
            }
            emailValid = false;
        });


        $("#fullname").on("input",function(){

        let fullname = $(this).val();
        const lettersOnly = /^[A-Za-z]+(?:[ .'-][A-Za-z]+)*$/;
        if(fullname === ""){
            $("#fullnameError").text("Fullname is required.").css("color", "red").removeClass("d-none");
        }else if(fullname.length<5){
            $("#fullnameError").text("Too short.").css("color", "red").removeClass("d-none");
        }else if(!lettersOnly.test(fullname)){
            $("#fullnameError").text("Only letters are allowed.").css("color", "red").removeClass("d-none");
        }else {
        $("#fullnameError").addClass("d-none");
        fullnameValid = true;
        return;
      }
      fullnameValid = false;
        });

        $("#password").on("input", function () {
            let password = $(this).val();

            let regex = {
                upper: /[A-Z]/,
                lower: /[a-z]/,
                digit: /[0-9]/,
                special: /[!@#$%^&*(),.?":{}|<>]/,
                length: /^.{8,}$/
            };

            if (password.length === 0) {
                $("#passwordError").text("Password is required").removeClass("d-none");;
            } else if (!regex.length.test(password)) {
                $("#passwordError").text("Minimum 8 characters required").removeClass("d-none");;
            } else if (!regex.upper.test(password)) {
                $("#passwordError").text("At least 1 uppercase letter required").removeClass("d-none");;
            } else if (!regex.lower.test(password)) {
                $("#passwordError").text("At least 1 lowercase letter required").removeClass("d-none");;
            } else if (!regex.digit.test(password)) {
                $("#passwordError").text("At least 1 number required").removeClass("d-none");;
            } else if (!regex.special.test(password)) {
                $("#passwordError").text("At least 1 special character required").removeClass("d-none");;
            } else {
                $("#passwordError").addClass("d-none");
                passwordValid = true;
                return;
            }
            passwordValid = false;
        });

        $("#contactNumber").on("input", function() {
            const contact = $(this).val();
            const onlyDigits = /^[0-9]{10}$/; // Exactly 10 digits

            if (contact.length === 0) {
                $("#contactNumberError").text("Contact Number is required.").css("color", "red").removeClass("d-none");
                contactNumberValid = false;
            } else if (!onlyDigits.test(contact)) {
                $("#contactNumberError").text("Contact number must be exactly 10 digits and contain only numbers.").css("color", "red").removeClass("d-none");
                contactNumberValid = false;
            } else {
                $("#contactNumberError").addClass("d-none");
                contactNumberValid = true;
            }
    });

        $("form").on("submit", function(e) {
            if (
                !$("#fullnameError").hasClass("d-none") ||
                !$("#emailError").hasClass("d-none") ||
                !$("#passwordError").hasClass("d-none") ||
                !$("#contactNumberError").hasClass("d-none")
            ) {
                e.preventDefault();
                alert("Please fix the highlighted errors.");
            }

    });

    </script>
    
</body>
</html>
