<?php
include("data.php"); 
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username= filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $email= filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS);
    $phonenb= filter_input(INPUT_POST, "phonenb", FILTER_SANITIZE_SPECIAL_CHARS);
    $password= filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
    $hash=password_hash($password, PASSWORD_BCRYPT);
    $verfiyusername=mysqli_query($conn,"select username from user where username ='$username'");
    $verfiyphonenb=mysqli_query($conn,"select phone from user where phone ='$phonenb'");
    $verfiyemail=mysqli_query($conn,"select email from user where email ='$email'");

    if(mysqli_num_rows($verfiyusername) !=0  || mysqli_num_rows($verfiyemail) !=0  || mysqli_num_rows($verfiyphonenb) !=0){
         echo "username or phone number or  email is already taken";
       
    }else{

    
    if(empty($username)){
        echo "enter a username";
    }elseif(empty($email)){
        echo "enter an email";
    }elseif(empty($phonenb)){
        echo "enter an phone number";
    }elseif(empty($password)){
        echo "enter a password";
    }else{
         $hash=password_hash($password, PASSWORD_BCRYPT);
        $sql="INSERT INTO user(username,phone,email, password) VALUES('$username','$phonenb','$email' ,'$hash')";
        $_SESSION['username'] = $username; 
       
       
        try{
            mysqli_query($conn,$sql);
            echo "registered successfully. Redirecting to the home page...";
            header("refresh:3;url=index.php"); 

        }catch(mysqli_sql_exception $e){
            echo "could not insert record: " . $e->getMessage();
        }
       
    }
    

    }
}
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>learnify - Sign Up</title>
  <link rel="stylesheet" href="sighnup.css">
  <script src="https://kit.fontawesome.com/608545112e.js" crossorigin="anonymous"></script>
</head>
<body>

<div class="header">
    <?php include("header.php") ?>
</div> 

  <div class="container">
    <div class="form-box">
      <h1 id="title">Sign Up</h1>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="input-group">
          <div class="input-field" id="nameField">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username" id="" placeholder="" required>
          </div>

          <div class="input-field">
            <i class="fa-solid fa-envelope"></i>
            <input type="email" name="email" id="email" placeholder="" required>
          </div>
          <div class="input-field">
          <i class="fa-solid fa-phone"></i>
            <input type="phonenb" name="phonenb" id="phonenb" placeholder="" required>
          </div>
          <div class="input-field">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="" required>
          </div>
        </div>

        <div class="btn-field">
          <input type="submit" name="submit" value="Sign Up">
        </div>
      </form>

      <p class="switch-form">Already have an account? <a href="login.php">Sign In</a></p>
    </div>
  </div>
</body>
</html>
