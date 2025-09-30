<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OneAll Computer Store</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f9;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    h1 {
      color: #333;
      margin-bottom: 20px;
    }
    .buttons {
      display: flex;
      gap: 20px;
    }
    a {
      text-decoration: none;
      background: #4CAF50;
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      font-size: 16px;
      transition: background 0.3s ease;
    }
    a:hover {
      background: #45a049;
    }
    .admin {
      background: #007BFF;
    }
    .admin:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  <h1>Welcome to OneAll Store</h1>
  <div class="buttons">
    <a href="user/">Go to User Site</a>
    <a href="admin/" class="admin">Go to Admin Site</a>
  </div>
</body>
</html>
