

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST CU Portal Role Selection</title>
    <link rel="shortcut icon" type="image/x-icon" href="https://mustcu.or.ke/images/resized_image_1.jpg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0207ba;
            --accent-orange: #ff7900;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --shadow: rgba(2, 7, 186, 0.15);
        }

        body {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1e3c72 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px var(--shadow);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-orange));
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-image {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .logo-img {
            width: 120px;
            height: auto;
            max-height: 80px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(2, 7, 186, 0.3);
            transition: transform 0.3s ease;
            background: var(--white);
            padding: 10px;
        }

        .logo-img:hover {
            transform: scale(1.05);
        }

        .logo-fallback {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-orange));
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(2, 7, 186, 0.3);
            transition: transform 0.3s ease;
        }

        .logo-fallback:hover {
            transform: scale(1.05);
        }

        .logo-fallback i {
            font-size: 2rem;
            color: var(--white);
        }

        .company-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin: 0;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            margin-top: 5px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-danger {
            background-color: #ffe6e6;
            color: #d63384;
            border-left: 4px solid #d63384;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(2, 7, 186, 0.1);
            background-color: var(--white);
        }

        .form-select:hover {
            border-color: var(--accent-orange);
            background-color: var(--white);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-orange));
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(2, 7, 186, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .mb-3 {
            margin-bottom: 1.5rem !important;
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 30px 25px;
                margin: 10px;
            }
            .company-name {
                font-size: 1.5rem;
            }
            .logo-img {
                width: 100px;
                max-height: 70px;
            }
            .logo-fallback {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- PHP Error/Success Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle me-2"></i>
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Logo Section -->
        <div class="logo-container">
            <div class="logo-image">
                <img src="https://codeslibrary.mustcu.or.ke/Images/rond logo.png" alt="MUST CU Logo" class="logo-img">
                <div class="logo-fallback">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <h1 class="company-name">MUST CU Portal</h1>
            <p class="subtitle">Please Select your role to proceed to your dashboard</p>
        </div>

        <!-- Role Selection Form -->
        <form action="/process_role" method="POST">
            <div class="mb-3">
                <label for="role" class="form-label">
                    <i class="fas fa-user-tag me-2"></i>Select Your Role
                </label>
                <select name="role" id="role" class="form-select" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="member">Member</option>
                    <option value="leader">Leader</option>
                    <option value="admin">Admin</option>
                    <option value="associate">Associate</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-arrow-right me-2"></i>
                Proceed to Login
            </button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle logo image loading
        const logoImg = document.querySelector('.logo-img');
        const logoFallback = document.querySelector('.logo-fallback');

        if (logoImg && logoFallback) {
            logoImg.addEventListener('error', function() {
                this.style.display = 'none';
                logoFallback.style.display = 'inline-flex';
            });

            logoImg.addEventListener('load', function() {
                if (this.complete && this.naturalHeight !== 0) {
                    this.style.display = 'block';
                    logoFallback.style.display = 'none';
                } else {
                    this.style.display = 'none';
                    logoFallback.style.display = 'inline-flex';
                }
            });

            if (logoImg.complete) {
                if (logoImg.naturalHeight !== 0) {
                    logoImg.style.display = 'block';
                    logoFallback.style.display = 'none';
                } else {
                    logoImg.style.display = 'none';
                    logoFallback.style.display = 'inline-flex';
                }
            }
        }

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        });

        // Form validation feedback
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const inputs = form.querySelectorAll('select');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!input.checkValidity()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        input.classList.remove('is-valid');
                    } else {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }

        const inputs = document.querySelectorAll('.form-select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else if (this.value !== '') {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });

            input.addEventListener('input', function() {
                this.classList.remove('is-invalid', 'is-valid');
            });
        });
    </script>
</body>
</html>