<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - ClassKart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F5F5F5;
            color: #333;
        }

        /* Header */
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

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            width: auto;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #0A5033;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .search-bar {
            padding: 0.6rem 1.2rem;
            border: 1px solid #E0E0E0;
            border-radius: 25px;
            outline: none;
            width: 250px;
            transition: border 0.3s;
        }

        .search-bar:focus {
            border-color: #0A5033;
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            font-size: 1.5rem;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #0A5033;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Login Container */
        .login-container {
            min-height: calc(100vh - 300px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 5%;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 450px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-logo img {
            height: 60px;
            width: auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0A5033;
            box-shadow: 0 0 0 3px rgba(10, 80, 51, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #0A5033;
        }

        .remember-me label {
            font-size: 0.9rem;
            color: #666;
            cursor: pointer;
        }

        .forgot-password {
            color: #0A5033;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .forgot-password:hover {
            opacity: 0.8;
        }

        .btn-signin {
            width: 100%;
            background: #0A5033;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .btn-signin:hover {
            background: #084028;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(10, 80, 51, 0.3);
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        .signup-link a {
            color: #0A5033;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.3s;
        }

        .signup-link a:hover {
            opacity: 0.8;
        }

        /* Footer */
        footer {
            background-color: #F8F8F8;
            padding: 3rem 5% 2rem;
            margin-top: 3rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .footer-section p {
            line-height: 1.8;
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.6rem;
        }

        .footer-links a {
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: #0A5033;
            padding-left: 5px;
        }

        .social-icons {
            display: flex;
            gap: 0.8rem;
            margin-top: 1rem;
        }

        .social-icon {
            width: 35px;
            height: 35px;
            background: #E0E0E0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .social-icon:hover {
            background: #0A5033;
            color: white;
            transform: translateY(-3px);
        }

        .newsletter-form {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .newsletter-form input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            outline: none;
            font-family: 'Poppins', sans-serif;
        }

        .newsletter-form input:focus {
            border-color: #0A5033;
        }

        .newsletter-form button {
            padding: 0.8rem 1.5rem;
            background-color: #0A5033;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .newsletter-form button:hover {
            background-color: #084028;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #E0E0E0;
            color: #666;
            font-size: 0.9rem;
        }

        /* Error Message */
        .error-message {
            background: #fee;
            color: #c33;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .search-bar {
                width: 150px;
            }

            .nav-links {
                gap: 1.5rem;
            }

            .login-card {
                padding: 2rem;
            }
        }

        @media (max-width: 480px) {
            .logo img {
                height: 40px;
            }

            .search-bar {
                display: none;
            }

            .login-card {
                padding: 1.5rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
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
            <div class="nav-right">
                <input type="text" class="search-bar" placeholder="Search products...">
                <div class="cart-icon">
                    🛒
                    <span class="cart-count">0</span>
                </div>
                <a href="login.html">Login</a>
            </div>
        </nav>
    </header>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <img src="images/logo.png" alt="ClassKart Logo">
            </div>
            
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your ClassKart account</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message show"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php else: ?>
                <div class="error-message" id="errorMessage"></div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="login_process.php">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn-signin">Sign In</button>

                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <div class="logo">
                    <img src="images/logo.png" alt="ClassKart Logo" style="height: 40px; margin-bottom: 1rem;">
                </div>
                <p>Your One-Stop Shop for Learning. Quality educational materials for students, teachers, and parents.</p>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.html">Shop</a></li>
                    <li><a href="index.html#about">About Us</a></li>
                    <li><a href="contact.html">Contact</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe to get updates on new products and exclusive offers.</p>
                <form class="newsletter-form" onsubmit="return false;">
                    <input type="email" placeholder="Your email" required>
                    <button type="submit"><i class="fas fa-envelope"></i> Subscribe</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2025 ClassKart. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Client-side form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');

            // Basic validation
            if (!email || !password) {
                e.preventDefault();
                errorMessage.textContent = 'Please fill in all fields.';
                errorMessage.classList.add('show');
                return false;
            }

            // Email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                errorMessage.textContent = 'Please enter a valid email address.';
                errorMessage.classList.add('show');
                return false;
            }

            errorMessage.classList.remove('show');
        });

        // Hide error message when user starts typing
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                document.getElementById('errorMessage').classList.remove('show');
            });
        });
    </script>
</body>
</html>

        }



        .footer-links {

            list-style: none;

        }



        .footer-links li {

            margin-bottom: 0.6rem;

        }



        .footer-links a {

            color: #666;

            text-decoration: none;

            transition: all 0.3s;

            font-size: 0.9rem;

        }



        .footer-links a:hover {

            color: #0A5033;

            padding-left: 5px;

        }



        .social-icons {

            display: flex;

            gap: 0.8rem;

            margin-top: 1rem;

        }



        .social-icon {

            width: 35px;

            height: 35px;

            background: #E0E0E0;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #333;

            text-decoration: none;

            transition: all 0.3s;

            font-size: 0.9rem;

        }



        .social-icon:hover {

            background: #0A5033;

            color: white;

            transform: translateY(-3px);

        }



        .newsletter-form {

            display: flex;

            gap: 0.5rem;

            margin-top: 1rem;

        }



        .newsletter-form input {

            flex: 1;

            padding: 0.8rem 1rem;

            border: 1px solid #E0E0E0;

            border-radius: 8px;

            outline: none;

            font-family: 'Poppins', sans-serif;

        }



        .newsletter-form input:focus {

            border-color: #0A5033;

        }



        .newsletter-form button {

            padding: 0.8rem 1.5rem;

            background-color: #0A5033;

            color: white;

            border: none;

            border-radius: 8px;

            font-weight: 600;

            cursor: pointer;

            transition: all 0.3s;

            font-family: 'Poppins', sans-serif;

            display: flex;

            align-items: center;

            gap: 0.5rem;

        }



        .newsletter-form button:hover {

            background-color: #084028;

        }



        .footer-bottom {

            text-align: center;

            padding-top: 2rem;

            border-top: 1px solid #E0E0E0;

            color: #666;

            font-size: 0.9rem;

        }



        /* Error Message */

        .error-message {

            background: #fee;

            color: #c33;

            padding: 0.8rem;

            border-radius: 8px;

            margin-bottom: 1rem;

            font-size: 0.9rem;

            display: none;

        }



        .error-message.show {

            display: block;

        }



        /* Responsive */

        @media (max-width: 768px) {

            .search-bar {

                width: 150px;

            }



            .nav-links {

                gap: 1.5rem;

            }



            .login-card {

                padding: 2rem;

            }

        }



        @media (max-width: 480px) {

            .logo img {

                height: 40px;

            }



            .search-bar {

                display: none;

            }



            .login-card {

                padding: 1.5rem;

            }



            .login-header h1 {

                font-size: 1.5rem;

            }

        }

    </style>

</head>

<body>

    <!-- Header -->

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

            <div class="nav-right">

                <input type="text" class="search-bar" placeholder="Search products...">

                <div class="cart-icon">

                    🛒

                    <span class="cart-count">0</span>

                </div>

                <a href="login.html">Login</a>

            </div>

        </nav>

    </header>



    <!-- Login Container -->

    <div class="login-container">

        <div class="login-card">

            <div class="login-logo">

                <img src="images/logo.png" alt="ClassKart Logo">

            </div>

            

            <div class="login-header">

                <h1>Welcome Back</h1>

                <p>Sign in to your ClassKart account</p>

            </div>



            <div class="error-message" id="errorMessage"></div>

             <form id="loginForm" method="POST" action="login_process.php">



                <div class="form-group">

                    <label for="email">Email</label>

                    <input type="email" id="email" name="email" required>

                </div>



                <div class="form-group">

                    <label for="password">Password</label>

                    <input type="password" id="password" name="password" required>

                </div>



                <div class="form-options">

                    <div class="remember-me">

                        <input type="checkbox" id="remember" name="remember">

                        <label for="remember">Remember me</label>

                    </div>

                    <a href="forgot_password.html" class="forgot-password">Forgot password?</a>

                </div>



                <button type="submit" class="btn-signin">Sign In</button>



                <div class="signup-link">

                    Don't have an account? <a href="signup.html">Sign up</a>

                </div>

            </form>

        </div>

    </div>



    <!-- Footer -->

    <footer>

        <div class="footer-content">

            <div class="footer-section">

                <div class="logo">

                    <img src="images/logo.png" alt="ClassKart Logo" style="height: 40px; margin-bottom: 1rem;">

                </div>

                <p>Your One-Stop Shop for Learning. Quality educational materials for students, teachers, and parents.</p>

                <div class="social-icons">

                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>

                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>

                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>

                </div>

            </div>



            <div class="footer-section">

                <h3>Quick Links</h3>

                <ul class="footer-links">

                    <li><a href="index.html">Shop</a></li>

                    <li><a href="index.html#about">About Us</a></li>

                    <li><a href="contact.html">Contact</a></li>

                </ul>

            </div>



            <div class="footer-section">

                <h3>Newsletter</h3>

                <p>Subscribe to get updates on new products and exclusive offers.</p>

                <form class="newsletter-form" onsubmit="return false;">

                    <input type="email" placeholder="Your email" required>

                    <button type="submit"><i class="fas fa-envelope"></i> Subscribe</button>

                </form>

            </div>

        </div>



        <div class="footer-bottom">

            <p>© 2025 ClassKart. All rights reserved.</p>

        </div>

    </footer>



    <script>

        // Client-side form validation

        document.getElementById('loginForm').addEventListener('submit', function(e) {

            const email = document.getElementById('email').value;

            const password = document.getElementById('password').value;

            const errorMessage = document.getElementById('errorMessage');



            // Basic validation

            if (!email || !password) {

                e.preventDefault();

                errorMessage.textContent = 'Please fill in all fields.';

                errorMessage.classList.add('show');

                return false;

            }



            // Email format validation

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {

                e.preventDefault();

                errorMessage.textContent = 'Please enter a valid email address.';

                errorMessage.classList.add('show');

                return false;

            }



            errorMessage.classList.remove('show');

        });



        // Hide error message when user starts typing

        document.querySelectorAll('input').forEach(input => {

            input.addEventListener('input', function() {

                document.getElementById('errorMessage').classList.remove('show');

            });

        });

    </script>

</body>

</html>
