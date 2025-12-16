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
                                <li class="nav-item"><a class="nav-link" href="leadership.php">Leadership</a></li>
                                <li class="nav-item submenu"><a class="nav-link" href="#">Pages</a>
                                    <ul>
                                        <li class="nav-item"><a class="nav-link" href="Weekly Activities.php">Weekly
                                                Activities</a></li>
                                        <li class="nav-item"><a class="nav-link" href="blog.php">Blogs</a></li>

                                        <li class="nav-item"><a class="nav-link" href="constitituion.php">Our Constitution</a>
                                        </li>
                                        <li class="nav-item"><a class="nav-link" href="ministries.php">Ministries</a>
                                        </li>

                                        <li class="nav-item"><a class="nav-link" href="leadership.php">Leadership</a></li>
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
                        <h1 class="text-anime-style-2" data-cursor="-opaque">Weekly Activities</h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="pastor.php">home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Weekly Activities</li>
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
        .schedule-container {
            max-width: 100%;
            overflow-x: auto;
            padding: 20px;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            background-color: black;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
            margin-bottom: 20px;
        }

        .schedule-table th,
        .schedule-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .day-column {
            background-color: #0207ba;
            font-weight: bold;
            min-width: 100px;
            color: rgb(255, 255, 255);
        }

        .header-row {
            background-color: #ff7900;
            color: white;
            font-weight: bold;
        }

        .sub-header {
            background-color: #0207ba;
            font-style: italic;
            font-weight: bold;
        }

        .time-cell {
            background-color: #ff7900;
            color: white;
            min-width: 120px;
        }

        .activity-row {
            background-color: rgb(255, 255, 255);
        }

        .activity-cell {
            min-width: 200px;
        }

        .venue-cell {
            min-width: 100px;
        }

        @media (max-width: 768px) {
            .schedule-container {
                padding: 10px;
            }

            .schedule-table th,
            .schedule-table td {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>
    </head>

    <body>
        <div class="schedule-container">
            <table class="schedule-table">
                <tr class="header-row">
                    <th width="150"><strong>DAY</strong></th>
                    <th><strong>ACTIVITY</strong></th>
                    <th><strong>VENUE</strong></th>
                    <th><strong>TIME</strong></th>
                </tr>
                <!-- MONDAY -->
                <tr class="activity-row">
                    <th rowspan="3" class="day-column"><strong>MONDAY</strong></th>
                    <td class="activity-cell">BIBLE STUDY</td>
                    <td class="venue-cell">G-SQUARE</td>
                    <td class="time-cell">5:00 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">INTERMIDIATE BAND PRACTICE</td>
                    <td class="venue-cell">G-SQUARE</td>
                    <td class="time-cell">7:00 PM - 8:20 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">IT & PUBLICITY TRAINING</td>
                    <td class="venue-cell">AC 17</td>
                    <td class="time-cell">7:00 PM - 8:20 PM</td>
                </tr>

                <!-- TUESDAY -->
                <tr class="activity-row">
                    <th rowspan="8" class="day-column"><strong>TUESDAY</strong></th>
                    <th colspan="3" class="sub-header"><strong><i>BLOCK A PRACTICES</i></strong></th>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">PRAISE AND WORSHIP</td>
                    <td class="venue-cell">G-SQUARE</td>
                    <td class="time-cell">5:00 PM - 6:20 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">CHOIR</td>
                    <td class="venue-cell">AB 13</td>
                    <td class="time-cell">5:00 PM - 6:20 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">CATERING</td>
                    <td class="venue-cell">ECB 01</td>
                    <td class="time-cell">5:00 PM - 6:20 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">CREATIVE</td>
                    <td class="venue-cell">ECB 02</td>
                    <td class="time-cell">5:00 PM - 6:20 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">USHERING</td>
                    <td class="venue-cell">ECB 03</td>
                    <td class="time-cell">5:00 PM - 6:20 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">DECOR</td>
                    <td class="venue-cell">ECB 21</td>
                    <td class="time-cell">5:00 PM - 6:20 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><b>FFC CLASS</b></td>
                    <td class="venue-cell"><b>ECB 04</b></td>
                    <td class="time-cell"><b>7:00 PM - 8:20 PM</b></td>
                </tr>

                <!-- WEDNESDAY -->
                <tr class="activity-row">
                    <th rowspan="7" class="day-column"><strong>WEDNESDAY</strong></th>
                    <td colspan="3" class="sub-header">&nbsp;</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><i><b>Free Wednesday</b></i></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">1<sup>st</sup> week of the month</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><i><b>Years' fellowship (Anzafyt, Endeleafyt and VukaFit)</b></i></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">2<sup>nd</sup> week of the month</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><i><b>Leaders' prayer and fasting, Anzafyt and VukaFit</b></i></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">3<sup>rd</sup> week of the month</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><i><b>Free Wednesday</b></i></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">4<sup>th</sup> week of the month</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell" rowspan="2">MID-WEEK INTERCESSORY</td>
                    <td class="venue-cell" rowspan="2">To our respective prayer points</td>
                    <td class="time-cell">Outside school (7:30PM - 8:30PM)</td>
                </tr>
                <tr class="activity-row">
                    <td class="time-cell">In school (7:00PM - 8:00PM)</td>
                </tr>

                <!-- THURSDAY -->
                <tr class="activity-row">
                    <th rowspan="6" class="day-column"><strong>THURSDAY</strong></th>
                    <th colspan="3" class="sub-header"><strong><i>UNION PRAYER AND FASTING "Conclusion shall
                                be"</i></strong></th>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><i><b>Evangelism</b></i></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">1<sup>st</sup> week of the month</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><i><b>Welfare meeting</b></i></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">2<sup>nd</sup> week of the month</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><i><b>Welfare meeting</b></i></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">3<sup>rd</sup> week of the month</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><i><b>Brothers and sisters' forum</b></i></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">4<sup>th</sup> week of the month</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell"><b>BEST-P CLASSES</b></td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">7:00 PM - 8:20 PM</td>
                </tr>

                <!-- FRIDAY -->
                <tr class="activity-row">
                    <th rowspan="4" class="day-column"><strong>FRIDAY</strong></th>
                    <td colspan="3" class="sub-header">&nbsp;</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">MINISTERIAL DEVOTIONS</td>
                    <td class="venue-cell">&nbsp;</td>
                    <td class="time-cell">5:00 PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell" rowspan="2">REVIVAL SERVICE</td>
                    <td class="venue-cell" rowspan="2">MPH</td>
                    <td class="time-cell">QUIET TIME FROM 6:10PM-6:20PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="time-cell">SERVICE FROM 6:20-8:30PM</td>
                </tr>

                <!-- SATURDAY -->
                <tr class="activity-row">
                    <th rowspan="5" class="day-column"><strong>SATURDAY</strong></th>
                    <td colspan="3" class="sub-header">&nbsp;</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">IT & PUBLICITY TRAINING</td>
                    <td class="venue-cell">AC 17</td>
                    <td class="time-cell">2:00PM - 4:00PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">BEGINNERS BAND PRACTICE</td>
                    <td class="venue-cell">G-SQUARE GENERAL</td>
                    <td class="time-cell">2:00PM - 4:00PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">BAND PRACTISE</td>
                    <td class="venue-cell">G-SQUARE GENERAL</td>
                    <td class="time-cell">4:00PM - 6:00PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">MEGA INTERCESSORY</td>
                    <td class="venue-cell">ECA 05</td>
                    <td class="time-cell">7:00PM - 8:45PM</td>
                </tr>

                <!-- SUNDAY -->
                <tr class="activity-row">
                    <th rowspan="3" class="day-column"><strong>SUNDAY</strong></th>
                    <td colspan="3" class="sub-header">&nbsp;</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">FIRST SERVICE</td>
                    <td class="venue-cell">MPH</td>
                    <td class="time-cell">7:20AM - 9:40PM</td>
                </tr>
                <tr class="activity-row">
                    <td class="activity-cell">SECOND SERVICE</td>
                    <td class="venue-cell">G-SQUARE</td>
                    <td class="time-cell">10:40AM-1:00PM</td>
                </tr>
            </table>
        </div>

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
                                    <li><a href="https://www.instagram.com/mustcu_org?igsh=OWN5NHJlYTljeGI5"
                                            target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
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
                                <li><a href="leadership.php">leadership</a></li>
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

</body>

</html>