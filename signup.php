<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

//si formulaire envoyée 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']); //trim supprime espace inutilenettoiye donnee
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // tableau stocker erreur
    $errors = [];
    //verifier validation des champs
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    // Check if email already exists
    $users_file = 'data/users.json';//stockage users fichier jason
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);// lit fichier jason le transfoem en tableau php
        foreach ($users as $user) { //parcourir utilisateur
            if ($user['email'] === $email) {
                $errors[] = "Email already exists";
                break;
            }
        }
    }
    
    if (empty($errors)) {
        // Create new user
        $new_user = [
            'id' => count($users) + 1,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => 'user',
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];
        
        $users[] = $new_user;//ajoute user
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));//transforme php en jason ecrit dans le fichier pour save it
        
        $_SESSION['success'] = "Registration successful! Please login.";
        header('Location: login.php');//passe page login 
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - CoRide</title>
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
            max-width: 500px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
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
                <h1>Create Account</h1>
                <p>Join our carpooling community</p>
            </div>
            
            <div class="auth-body">
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
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required 
                                   value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required 
                                   value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn-auth">Create Account</button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>
    
    <script src="js/common.js"></script>
    <script src="js/auth.js"></script>
</body>
</html>
