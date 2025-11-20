<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ClassKart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F5F5F5;
            color: #333;
        }

        header {
            background-color: #FFFFFF;
            padding: 1rem 5%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo img { height: 50px; }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .nav-links a:hover { color: #0A5033; }

        .signup-container {
            min-height: calc(100vh - 250px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 5%;
        }

        .signup-card {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 500px;
        }

        .signup-logo img { height: 60px; }

        .signup-header { text-align: center; margin-bottom: 2rem; }
        .signup-header h1 { font-size: 1.8rem; color: #333; }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: .5rem; font-weight: 500; }

        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-size: .95rem;
        }

        .btn-signup {
            width: 100%;
            background: #0A5033;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-signup:hover {
            background: #084028;
        }

        .signin-link { text-align: center; margin-top: 1.5rem; }

        /* PHP message styles */
        .error-message {
            background: #fee;
            color: #c33;
            padding: .8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: .9rem;
        }

        .success-message {
            background: #efe;
            color: #3a3;
            padding: .8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: .9rem;
        }

        footer {
            background-color: #F8F8F8;
            padding: 2rem 5%;
            margin-top: 3rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
            .signup-card { padding: 2rem; }
        }
    </style>
</head>

<body>

    <header>
        <nav>
            <div class="logo">
                <img src="images/logo.png" alt="ClassKart Logo">
            </div>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="index.html#shop">Shop</a></li>
                <li><a href="index.html#about">About</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
    </header>

    <div class="signup-container">
        <div class="signup-card">

            <!-- LOGO -->
            <div class="signup-logo">
                <img src="images/logo.png" alt="ClassKart Logo">
            </div>

            <!-- HEADER -->
            <div class="signup-header">
                <h1>Create Account</h1>
                <p>Join ClassKart and start learning today</p>
            </div>

            <!-- PHP ERROR/SUCCESS MESSAGES -->
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="POST" action="signup_process.php">
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn-signup">Sign Up</button>

                <div class="signin-link">
                    Already have an account? <a href="login.html">Sign in</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>© 2025 ClassKart. All rights reserved.</p>
    </footer>

</body>
</html>
