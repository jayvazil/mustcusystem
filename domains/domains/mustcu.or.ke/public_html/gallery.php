<!DOCTYPE html>
<html lang="zxx">

<head>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="Awaiken">
    <!-- Page Title -->
    <title>Meru University Christian Union</title>
    <!-- Favicon Icon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/resized_image_1.jpg">
    <!-- Google Fonts Css-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="../../css2-1?family=Fira+Sans+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- Bootstrap Css -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <!-- SlickNav Css -->
    <link href="css/slicknav.min.css" rel="stylesheet">
    <!-- Swiper Css -->
    <link rel="stylesheet" href="css/swiper-bundle.min.css">
    <!-- Font Awesome Icon Css-->
    <link href="css/all.css" rel="stylesheet" media="screen">
    <!-- Animated Css -->
    <link href="css/animate.css" rel="stylesheet">
    <!-- Magnific Popup Core Css File -->
    <link rel="stylesheet" href="css/magnific-popup.css">
    <!-- Mouse Cursor Css File -->
    <link rel="stylesheet" href="css/mousecursor.css">
    <!-- Audio Css File -->
    <link rel="stylesheet" href="css/plyr.css">
    <!-- Main Custom Css -->
    <link href="css/custom.css" rel="stylesheet" media="screen">
</head>

<body>

    <!-- Preloader Start -->
    <div class="preloader">
        <div class="loading-container">
            <div class="loading"></div>
            <div id="loading-icon"><img src="images/resized_image_1.jpg" alt=""></div>
        </div>
    </div>
    <!-- Preloader End -->

    <!-- Header Start -->
    <header class="main-header">
        <div class="header-sticky">
            <nav class="navbar navbar-expand-lg">
                <div class="container">
                    <!-- Logo Start -->
                    <a class="navbar-brand" href="index.php">
                        <img src="images/resized_image_1.jpg" alt="Logo">
                    </a>
                    <!-- Logo End -->


                    <div class="collapse navbar-collapse main-menu">
                        <div class="nav-menu-wrapper">
                            <ul class="navbar-nav mr-auto" id="menu">
                                <li class="nav-item"><a class="nav-link" href="index.php">Home</a>
                                <li class="nav-item"><a class="nav-link" href="about.php">About Us</a>
                                <li class="nav-item"><a class="nav-link" href="pastor.php">Leadership</a></li>
                                <li class="nav-item submenu"><a class="nav-link" href="#">Pages</a>
                                    <ul>
                                        <li class="nav-item"><a class="nav-link" href="Weekly Activities.php">Weekly
                                                Activities</a></li>
                                        <li class="nav-item"><a class="nav-link" href="blog.php">Blogs</a></li>

                                        <li class="nav-item"><a class="nav-link" href="constitituion.php">Our Constitution</a>
                                        </li>
                                        <li class="nav-item"><a class="nav-link" href="ministries.php">Ministries</a>
                                        </li>

                                        <li class="nav-item"><a class="nav-link" href="pastor.php">Leadership</a></li>
                                        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>

                                    </ul>
                                </li>
                                <!-- buttons for small devices i.e admin and register -->
                                <li class="nav-item"><a class="nav-link" href="contact.php">Contact Us</a></li>
                                <li class="nav-item highlighted-menu"><a class="header-btn d-inline-flex"
                                        href="adminDashboard/login.php">Admin</li>
                                <li class="nav-item highlighted-menu"><a class="header-btn d-inline-flex"
                                        href="form 4.php">Register</li>

                            </ul>
                        </div>

                        <!-- Let’s Start Button Start -->
                        <div class="header-btn d-inline-flex">
                            <a href="adminDashboard/login.php"
                                class="btn-default btn-highlighted"><span>Admin</span></a>
                        </div>

                        <!-- Let’s Start Button Start -->
                        <div class="header-btn d-inline-flex">
                            <a href="form 4.php" class="btn-default">Register</a>
                        </div>
                        <!-- Let’s Start Button End -->
                    </div>
                    <!-- Main Menu End -->
                    <div class="navbar-toggle"></div>
                </div>
            </nav>
            <div class="responsive-menu"></div>
        </div>
    </header>
    <!-- Header End -->



    <!-- Page Header Start -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <!-- Page Header Box Start -->
                    <div class="page-header-box">
                        <h1 class="text-anime-style-2" data-cursor="-opaque">Gallery</h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="gallery.php">home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">gallery</li>
                            </ol>
                        </nav>
                    </div>
                    <!-- Page Header Box End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            stroke: #0207ba;
            stroke-width: 5px;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
        }

        .gallery-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            color: white;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .image-overlay {
            opacity: 1;
            stroke: #0207ba;
            stroke-width: 5px;
        }

        .image-title {
            font-size: 1.2em;
            margin-bottom: 8px;
        }

        .image-description {
            font-size: 0.9em;
            line-height: 1.4;
        }

        /* Tablet Responsive */
        @media screen and (max-width: 768px) {
            .gallery {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Mobile Responsive */
        @media screen and (max-width: 480px) {
            .gallery {
                grid-template-columns: 1fr;
            }

            .gallery-item img {
                height: 250px;
            }
        }
    </style>



    <div class="gallery-container">
        <div class="gallery">
            <div class="gallery-item">
                <img src="images/service 2.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">MUST CU Service</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/Funday 22.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">Must Cu Funday</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/Funday 11.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">Must Cu Funday</h3>
                   
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/agm 6.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">MUST CU AGM</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/choir 1.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">MUST CU Choir</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/hero-bg.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">Music Ministry</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/service bg 14.JPG" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">FORMER LEADERSHIP</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/integrity 5.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">INTEGRITY WALK</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/agm 3.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">MUST CU AGM</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/leaders 1.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">LEADERS</h3>
                   
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/IT and Publicity.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">IT & Publicity</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/integrity.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">INTEGRITY WALK</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/service bg 99.JPG" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">MUST SERVICE</h3>
                    
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/service bg 14.JPG" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">MUST CU SERVICE</h3>
                   
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/worship 4.jpg" alt="MUST CU">
                <div class="image-overlay">
                    <h3 class="image-title">WORSHIP EXPERIENCE</h3>
                    
                </div>
            </div>
        </div>
    </div>
</body>

<!-- Footer Start -->
<footer class="main-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <!-- About Footer Start -->
                <div class="about-footer">
                    <!-- Footer Logo Start -->
                    <div class="footer-logo">
                        <img src="images/resized_image_1.jpg" alt="">
                    </div>
                    <!-- Footer Logo End -->

                    <!-- About Footer Content Start -->
                    <div class="about-footer-content">
                        <p>Meru University of Science and Technology Christian Union</p>
                    </div>
                    <!-- Footer Social Links Start -->
                    <div class="footer-social-links">
                        <ul>
                            <li><a href="https://www.facebook.com/mustcuorg" target="_blank"><i
                                        class="fa-brands fa-facebook-f"></i></a></li>
                            <li><a href="https://www.instagram.com/mustcu_org?igsh=OWN5NHJlYTljeGI5" target="_blank"><i
                                        class="fa-brands fa-instagram"></i></a></li>
                            <li><a href="https://www.tiktok.com/@mustcuorg" target="_blank"><i
                                        class="fa-brands fa-tiktok"></i></a></li>
                            <li><a href="https://www.youtube.com/@MUSTCUORG" target="_blank"><i
                                        class="fa-brands fa-youtube"></i></a></li>
                        </ul>
                    </div>
                    <!-- Footer Social Links End -->

                </div>
                <!-- About Footer End -->
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <!-- About Links Start -->
                <div class="footer-links">
                    <h3>quick links</h3>
                    <ul>
                        <li><a href="index.php">home</a></li>
                        <li><a href="about.php">obout us</a></li>
                        <li><a href="pastor.php">leadership</a></li>
                        <li><a href="ministries.php">ministries</a></li>
                        <li><a href="form.php">register</a></li>
                        </u>
                </div>
                <!-- About Links End -->
            </div>

            <div class="col-lg-3 col-md-4 col-6">
                <!-- About Links Start -->
                <div class="footer-links">
                    <h3>our services</h3>
                    <ul>
                        <li><a href="contact.php">contact us</a></li>
                        <li><a href="#">special events</a></li>
                        <li><a href="#">online services</a></li>
                        <li><a href="blog.php">Blogs</a></li>
                        <li><a href="#">sunday worship</a></li>
                    </ul>
                </div>
                <!-- About Links End -->
            </div>

            <div class="col-lg-3 col-md-5">
                <!-- About Links Start -->
                <div class="footer-contact">
                    <h3>contact</h3>
                    <!-- Footer Contact Details Start -->
                    <div class="footer-contact-details">
                        <!-- Footer Info Box Start -->
                        <div class="footer-info-box">
                            <div class="icon-box">
                                <img src="images/icon-phone.svg" alt="">
                            </div>
                            <div class="footer-info-box-content">
                                <p>(+254) 795398942</p>
                            </div>
                        </div>
                        <!-- Footer Info Box End -->

                        <!-- Footer Info Box Start -->
                        <div class="footer-info-box">
                            <div class="icon-box">
                                <img src="images/icon-mail.svg" alt="">
                            </div>
                            <div class="footer-info-box-content">
                                <p>mustcu21@gmail.com</p>
                                <p>info@mustcu.or.ke</p>
                            </div>
                        </div>
                        <!-- Footer Info Box End -->

                        <!-- Footer Info Box Start -->
                        <div class="footer-info-box">
                            <div class="icon-box">
                                <img src="images/icon-location.svg" alt="">
                            </div>
                            <div class="footer-info-box-content">
                                <p>Meru University, Main Campus,Meru</p>
                            </div>
                        </div>
                        <!-- Footer Info Box End -->
                    </div>
                    <!-- Footer Contact Details End -->
                </div>
                <!-- About Links End -->
            </div>
        </div>

        <!-- Footer Copyright Section Start -->
        <div class="footer-copyright">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6">
                    <!-- Footer Copyright Start -->
                    <div class="footer-copyright-text">
                        <p>Copyright @2025 MUSTCU All Rights Reserved.</p>
                    </div>
                    <!-- Footer Copyright End -->
                </div>

                <div class="col-lg-6 col-md-6">
                    <!-- Footer Social Link Start -->
                    <div class="footer-privacy-policy">
                        <ul>
                            <li><a href="termsandpolicy.php">term & condition</a></li>
                            <li><a href="contact.php">support</a></li>
                            <li><a href="#">privacy policy</a></li>
                        </ul>
                    </div>
                    <!-- Footer Social Link End -->
                </div>
            </div>
        </div>
        <!-- Footer Copyright Section End -->
    </div>
</footer>
<!-- Footer End -->

<!-- Jquery Library File -->
<script src="js/jquery-3.7.1.min.js"></script>
<!-- Bootstrap js file -->
<script src="js/bootstrap.min.js"></script>
<!-- Validator js file -->
<script src="js/validator.min.js"></script>
<!-- SlickNav js file -->
<script src="js/jquery.slicknav.js"></script>
<!-- Swiper js file -->
<script src="js/swiper-bundle.min.js"></script>
<!-- Counter js file -->
<script src="js/jquery.waypoints.min.js"></script>
<script src="js/jquery.counterup.min.js"></script>
<!-- Magnific js file -->
<script src="js/jquery.magnific-popup.min.js"></script>
<!-- SmoothScroll -->
<script src="js/SmoothScroll.js"></script>
<!-- Parallax js -->
<script src="js/parallaxie.js"></script>
<!-- MagicCursor js file -->
<script src="js/gsap.min.js"></script>
<script src="js/magiccursor.js"></script>
<!-- Text Effect js file -->
<script src="js/SplitText.js"></script>
<script src="js/ScrollTrigger.min.js"></script>
<!-- YTPlayer js File -->
<script src="js/jquery.mb.YTPlayer.min.js"></script>
<!-- Audio js File -->
<script src="js/plyr.js"></script>
<!-- Wow js file -->
<script src="js/wow.js"></script>
<!-- Main Custom js file -->
<script src="js/function.js"></script>
<script src="../../assets/js/theme-panel.js"></script>
</body>

</html>