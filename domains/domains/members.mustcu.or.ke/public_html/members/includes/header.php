<?php
// Start session if not started (compatible with PHP 5.x)
if (!isset($_SESSION)) {
    session_start();
}
// Default role for testing if not set
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
if (!isset($_SESSION['user_id']) || $role !== 'member') {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/x-icon" href="https://mustcu.or.ke/images/resized_image_1.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            padding-top: 70px;
            transition: padding-left 0.3s ease;
        }
        .navbar-top {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
        }
        .navbar-brand {
            font-weight: bold;
            color: #0207ba !important;
            font-size: 1.5rem;
        }
        .navbar-brand:hover {
            color: #0207ba !important;
        }
        .btn-logout {
            background-color: #ff7900;
            border-color: #ff7900;
            color: #fff000 !important;
            padding: 8px 16px;
            border-radius: 8px;
        }
        .btn-logout:hover {
            background-color: #0207ba;
            border-color: #fff000;
        }
        .sidebar {
            position: fixed;
            top: 70px;
            left: 0;
            width: 250px;
            height: calc(100vh - 70px);
            background-color: #ffffff;
            border-right: 1px solid #e0e0e0;
            padding: 10px;
            transition: width 0.3s ease;
            z-index: 1020;
        }
        .sidebar.minimized {
            width: 60px;
            overflow-x: hidden;
        }
        .sidebar.minimized .nav-link span {
            display: none;
        }
        .sidebar.minimized .nav-link i {
            font-size: 1.2rem;
            margin: 0;
        }
        .sidebar .toggler {
            display: block;
            text-align: center;
            padding: 10px;
            cursor: pointer;
            color: #0207ba;
            font-size: 1.5rem;
        }
        .sidebar .toggler:hover {
            color: #0207ba;
        }
        .sidebar .nav-link {
            color: #333333;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1rem;
        }
        .sidebar .nav-link:hover {
            background-color: #e0e7ff;
            color: #0207ba !important;
        }
        .sidebar .nav-link.active {
            background-color: #0207ba;
            color: #fff000 !important;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 70px);
        }
        .content-wrapper.minimized {
            margin-left: 60px;
        }
        @media (max-width: 992px) {
            body {
                padding-left: 0;
            }
            .sidebar {
                width: 200px;
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .sidebar.minimized {
                width: 60px;
                transform: translateX(0);
            }
            .content-wrapper {
                margin-left: 0;
            }
            .navbar-toggler {
                display: block;
            }
        }
        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.2rem;
            }
            .btn-logout {
                padding: 6px 12px;
            }
        }
        /* Preloader Styles */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        .preloader.hidden {
            opacity: 0;
            pointer-events: none;
        }
         .spinner {
            width: 80px;
            height: 80px;
            border: 5px solid #ff7900;
            border-top: 5px solid #0207ba;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .spinner img {
            width: 50px;
            height: 50px;
            position: absolute;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Preloader -->
     <div class="preloader" id="preloader">
        <div class="spinner">
            <img src="https://mustcu.or.ke/images/resized_image_1.jpg" alt="Logo">
        </div>
    </div>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-top">
        <div class="container">
            <a class="navbar-brand" >MUST CU - Members</a>
            <button class="navbar-toggler" type="button" aria-label="Toggle sidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link btn-logout" href="/members/view_posts">Notifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-logout" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/index">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Sidebar Toggler -->
        <div class="toggler" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/members/dashboard">
                <i class="bi bi-house"></i><span>Dashboard</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>" href="/members/profile">
                <i class="bi bi-person"></i><span>Profile</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'view_posts.php' ? 'active' : ''; ?>" href="/members/view_posts">
                <i class="bi bi-envelope"></i><span>Notifications</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bestp-info.php' ? 'active' : ''; ?>" href="/members/bestp-info">
                <i class="bi bi-book"></i><span>BEST P</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'ffc.php' ? 'active' : ''; ?>" href="/members/ffc">
                <i class="bi bi-book"></i><span>FFC</span>
            </a>
           <!--- <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'orientation1.php' ? 'active' : ''; ?>" href="/members/orientation/orientation1">
                <i class="bi bi-people"></i><span>Orientation</span>
            </a>--->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'contact_admin.php' ? 'active' : ''; ?>" href="/members/contact_admin.php">
                <i class="bi bi-envelope"></i><span>Contact Admin</span>
            </a>
            <!--<a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'Nomination.php' ? 'active' : ''; ?>" href="/members/Nomination.php">
                <i class="bi bi-people"></i><span>Exec Nomination</span>
            </a>-->
            <!--<a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'Docket_Nominations.php' ? 'active' : ''; ?>" href="/members/Docket_Nominations.php">
                <i class="bi bi-people"></i><span>Ministerial Nomination</span>
            </a>-->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="nav-link btn-logout" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right"></i><span>Logout</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <i class="bi bi-question-circle me-2"></i>
                    Are you sure you want to log out?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="../logout.php" method="POST">
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper" id="contentWrapper">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preloader
        window.addEventListener('load', function () {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                setTimeout(() => {
                    preloader.classList.add('hidden');
                    // Remove preloader from DOM after transition
                    setTimeout(() => {
                        preloader.remove();
                    }, 500);
                }, 1000); // Minimum display time for preloader (1 second)
            }
        });

        // Toggle sidebar
        const toggleButton = document.querySelector('#sidebarToggle');
        const sidebar = document.querySelector('#sidebar');
        const contentWrapper = document.querySelector('#contentWrapper');
        const navbarToggler = document.querySelector('.navbar-toggler');

        // Load sidebar state from localStorage
        const isSidebarMinimized = localStorage.getItem('sidebarMinimized') === 'true';
        if (isSidebarMinimized) {
            sidebar.classList.add('minimized');
            contentWrapper.classList.add('minimized');
        }

        // Toggle sidebar width
        if (toggleButton && sidebar && contentWrapper) {
            toggleButton.addEventListener('click', function () {
                sidebar.classList.toggle('minimized');
                contentWrapper.classList.toggle('minimized');
                localStorage.setItem('sidebarMinimized', sidebar.classList.contains('minimized'));
            });
        }

        // Mobile toggle (show/hide sidebar)
        if (navbarToggler && sidebar) {
            navbarToggler.addEventListener('click', function () {
                if (sidebar.classList.contains('minimized')) {
                    sidebar.classList.remove('minimized');
                    contentWrapper.classList.remove('minimized');
                    localStorage.setItem('sidebarMinimized', false);
                } else {
                    sidebar.classList.toggle('active');
                }
            });
        }
    </script>
</body>
</html>