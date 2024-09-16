<?php
include("data.php"); 
session_start();  

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin'])) { 
  $username_login = filter_input(INPUT_POST, "username_login", FILTER_SANITIZE_SPECIAL_CHARS);
  $password_login = filter_input(INPUT_POST, "password_login", FILTER_SANITIZE_SPECIAL_CHARS);

 
  $sql_login = "SELECT * FROM user WHERE username='$username_login'";
  $result_login = mysqli_query($conn, $sql_login);

  if (mysqli_num_rows($result_login) == 1) { 
    $row_login = mysqli_fetch_assoc($result_login);
    if (password_verify($password_login, $row_login['password'])) { 
      $_SESSION['username'] = $username_login; 
    
      echo "loged in  successfully. Redirecting to the home page...";
      header("refresh:3;url=index.php");  
      exit();
    } else {
      echo "<p class='error'>Invalid username or password.</p>"; 
    }
  } else {
    echo "<p class='error'>Invalid username or password.</p>";
  }

  mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>learnify - Login</title>
  <link rel="stylesheet" href="login.css">
  <script src="https://kit.fontawesome.com/608545112e.js" crossorigin="anonymous"></script>
</head>
<body>
   <div class="header">
    <?php
   include("header.php");
  ?>
  </div>
  <div class="container">
    <div class="form-box">
      <h1 id="title">Sign In</h1>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="input-group">
          <div class="input-field" id="usernameField">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username_login" id="username" placeholder="" required>
          </div>

          <div class="input-field">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password_login" id="password" placeholder="" required>
          </div>
        </div>

        <div class="btn-field">
          <input type="submit" name="signin" value="Sign In">
        </div>
      </form>

      <p class="switch-form">Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
  </div>

</body>
</html>
