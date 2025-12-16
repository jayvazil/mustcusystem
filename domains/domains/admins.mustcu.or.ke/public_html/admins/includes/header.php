<?php
// Start session if not started (compatible with PHP 5.x)
if (!isset($_SESSION)) {
    session_start();
}
// Default role for testing if not set
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
if (!isset($_SESSION['user_id']) || !in_array($role, ['admin', 'super_admin'])) {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
            color: #FFFFFF !important;
            font-size: 1.5rem;
        }
        .navbar-brand:hover {
            color: #FF7900 !important;
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
            background-color: #000000;
            border-right: 1px solid #FFF000;
            padding: 10px;
            transition: width 0.3s ease;
            z-index: 1020;
        }
        .navbar-toggler {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050; /* higher than most elements */
    background-color: #fff000;
    padding: 10px 12px;
    border: none;
    border-radius: 6px;
    box-shadow: 0 4px 8px #ffffff;
    transition: background-color 0.3s ease;
}

.navbar-toggler:hover {
    background-color: #ff7900;
    cursor: pointer;
}

.navbar-toggler-icon {
    display: inline-block;
    width: 22px;
    height: 2px;
    background-color: white;
    position: relative;
}

.navbar-toggler-icon::before,
.navbar-toggler-icon::after {
    content: '';
    position: absolute;
    width: 22px;
    height: 2px;
    background-color: white;
    left: 0;
    transition: 0.3s ease;
}

.navbar-toggler-icon::before {
    top: -6px;
}

.navbar-toggler-icon::after {
    top: 6px;
}

        .sidebar.minimized {
            width: 90px;
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
            color: #fff000;
            font-size: 1.5rem;
        }
        .sidebar .toggler:hover {
            color: #ff7900;
        }
        .sidebar .nav-link {
            color: #FFF000;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link i {
            margin-right: 20px;
            font-size: 1rem;
        }
        .sidebar .nav-link:hover {
            background-color: #FFFFFF;
            color: #0207ba !important;
        }
        .sidebar .nav-link.active {
            background-color: #0207ba;
            color: #ff7900 !important;
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
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-top">
        <div class="container">
            <a class="navbar-brand" href="../admin/index.php">MUST CU - Admin</a>
            <button class="navbar-toggler" type="button" aria-label="Toggle sidebar">
                <span class="navbar-toggler-icon"></span>
           </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link btn-logout" href="/mark_read.php">Notifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-logout" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../SYSTEM PRO/index.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
    </nav>
     
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        
        <div class="toggler" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="../admins/dashboard.php">
                <i class="bi bi-house"></i><span>Dashboard</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'create_post.php' ? 'active' : ''; ?>" href="../admins/create_post.php">
                <i class="bi bi-megaphone"></i><span>Create Announcements</span>
            </a>
           
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage_positions.php' ? 'active' : ''; ?>" href="../admins/manage_positions.php">
                <i class="bi bi-person"></i><span>Register Leader</span>
            </a>
            <!--<a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'Documents.php' ? 'active' : ''; ?>" href="../admins/Documents.php">
                <i class="bi bi-person"></i><span>Documents</span>
            </a>--->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'approve_post.php' ? 'active' : ''; ?>" href="../admins/approve_post.php">
                <i class="bi bi-calendar-event"></i><span>Approve Events</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : ''; ?>" href="../admins/admin_dashboard.php">
                <i class="bi bi-envelope"></i><span>Update Members Portal</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'messaging_center.php' ? 'active' : ''; ?>" href="../admins/messaging_center.php">
                <i class="bi bi-envelope"></i><span>Emails Center</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'send_email.php' ? 'active' : ''; ?>" href="../admins/send_email.php">
                <i class="bi bi-calendar-plus"></i><span>Send Emails</span>
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'approve_email.php' ? 'active' : ''; ?>" href="../admins/approve_email.php">
                <i class="bi bi-calendar-plus"></i><span>Approve Emails</span>
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : ''; ?>" href="../admins/manage_users.php">
                <i class="bi bi-calendar-plus"></i><span>Manage Users l</span>
            </a>
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
                    <form action="../admins/logout.php" method="POST">
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
        document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const toggleButton = document.querySelector('#sidebarToggle');
    const sidebar = document.querySelector('#sidebar');
    const contentWrapper = document.querySelector('#contentWrapper');
    const navbarToggler = document.querySelector('.navbar-toggler');

    // Ensure toggler icons remain visible
    if (toggleButton) {
        toggleButton.style.display = 'block'; // Ensure sidebar toggle is always visible
        toggleButton.style.visibility = 'visible';
        toggleButton.style.opacity = '1';
    }
    if (navbarToggler) {
        navbarToggler.style.display = 'block'; // Ensure navbar toggler is always visible
        navbarToggler.style.visibility = 'visible';
        navbarToggler.style.opacity = '1';
    }

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
            // Ensure toggler remains visible after click
            toggleButton.style.display = 'block';
            toggleButton.style.visibility = 'visible';
            toggleButton.style.opacity = '1';
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
            // Ensure navbar toggler remains visible after click
            navbarToggler.style.display = 'block';
            navbarToggler.style.visibility = 'visible';
            navbarToggler.style.opacity = '1';
        });
    }
});
    </script>
</body>
</html>