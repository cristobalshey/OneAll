<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <!-- External CSS -->
  <link rel="stylesheet" href="login.css">
  <title>Login / Sign-Up</title>
</head>
<body>

  <div class="container" id="signUp" style="display: none;">
    <h1 class="form-title">Create Account</h1>
      <form method="post" action="register.php">
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="fName" id="fName" placeholder="First Name" required>
        <label for="fName">First Name</label>
      </div>
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="lName" id="lName" placeholder="Last Name" required>
        <label for="lName">Last Name</label>
      </div>
      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" id="emailSignUp" placeholder="Email" required>
        <label for="emailSignUp">Email</label>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="passwordSignUp" placeholder="Password" required>
        <i class="fas fa-eye-slash toggle-password" data-target="passwordSignUp"></i>
        <label for="passwordSignUp">Password</label>
      </div>
      <div class="input-group role-select">
    <i class="fas fa-user-tag"></i>
    <select name="role" id="role" required>
        <option value="" disabled selected hidden></option>
        <option value="user">User</option>
        <option value="facilitator">Facilitator</option>
        <option value="admin">Admin</option>
    </select>
    <label for="role">Select Role</label>
</div>


      <input type="submit" value="Sign Up" class="btn" name="signUp">
    </form>
    <p class="or">---------- or ----------</p>
    <div class="icons"><i class="fab fa-google"></i></div>
    <div class="link">
      <p>Already have an account?</p>
      <button id="signInButton">Sign In</button>
    </div>
  </div>
  

  <div class="container" id="signIn">
    <h1 class="form-title">Login</h1>
      <form method="post" action="register.php">
      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" id="emailSignIn" placeholder="Email" required>
        <label for="emailSignIn">Email</label>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="passwordSignIn" placeholder="Password" required>
        <i class="fas fa-eye-slash toggle-password" data-target="passwordSignIn"></i>
        <label for="passwordSignIn">Password</label>
      </div>
      <p class="forgot-password">
        <a href="#">Forgot Password?</a>
      </p>   
      <input type="submit" value="Sign In" class="btn" name="signIn">
    </form>
    <p class="or">---------- or ----------</p>
    <div class="icons"><i class="fab fa-google"></i></div>
    <div class="link">
      <p>Don't have an account yet?</p>
      <button id="signUpButton">Sign Up</button>
    </div>
  </div>

  <!-- External JS -->
  <script src="script.js"></script>
</body>
</html>


