<?php
session_start();


if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    
    $errors = [];
    
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    
    if (empty($errors)) {
        
        $users_file = 'data/users.json';
        if (file_exists($users_file)) {
            $users = json_decode(file_get_contents($users_file), true);
            
            foreach ($users as $user) {
                if ($user['email'] === $email && $user['password'] === $password) {
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    header('Location: dashboard.php');
                    exit();
                }
            }
        }
        
        $errors[] = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CoRide</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), var(--muted-color));
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            animation: slideUp 0.5s ease;
        }
        
        .auth-header {
            background: var(--primary-color);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .auth-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .auth-header p {
            opacity: 0.9;
        }
        
        .auth-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        .btn-auth {
            width: 100%;
            padding: 15px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-auth:hover {
            background: #a03048;
            transform: translateY(-2px);
        }
        
        .auth-footer {
            text-align: center;
            padding: 20px;
            background: var(--bg-light);
            border-top: 1px solid #e0e0e0;
        }
        
        .auth-footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background: #fee;
            color: var(--secondary-color);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .error-message ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .success-message {
            background: #e8f5e8;
            color: var(--muted-color);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--muted-color);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="nav-logo" style="justify-content: center; margin-bottom: 20px;">
                    <img src="images/logo.svg" alt="CoRide Logo" class="logo-img" style="width: 80px; height: 80px;">
                    <span class="logo-text">CoRide</span>
                </div>
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>
            
            <div class="auth-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn-auth">Sign In</button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
            </div>
        </div>
    </div>
    
    <script src="js/common.js"></script>
    <script src="js/auth.js"></script>
</body>
</html>
