<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ecotrack</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <link rel="stylesheet" href="landing.css">
</head>
<body>
  <!-- Floating Blob -->
  <div class="blob"></div>

  <!-- Glassmorphic Container -->
  <div class="landing-container">
    <!-- Centered Section -->
    <div class="center-content">
      <img src="images/re.png" alt="Ecotrack" class="landing-logo">
      <h1 class="title">Ecotrack</h1>
    </div>

    <!-- Bottom Section -->
    <div class="bottom-content">
      <button class="get-started-btn" onclick="redirectToLogin()">Get Started</button>
      <div class="recycling-text">Let’s make recycling fun together!</div>
    </div>
  </div>
  <script>
    function redirectToLogin() {
      window.location.href = "login.php"; 
    }
  </script>
</body>
</html>
