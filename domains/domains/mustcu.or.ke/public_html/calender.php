<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSTCU Calendar of Events</title>

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


<!-- Preloader Start -->
<div class="preloader">
    <div class="loading-container">
        <div class="loading"></div>
        <div id="loading-icon"><img src="images/resized_image_1.jpg" alt=""></div>
    </div>
</div>
<!-- Preloader End -->




<!-- Let’s Start Button Start -->
<div class="header-btn d-inline-flex">
    <a href="index.php" class="btn-default">Back to Home</a>
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

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #fff;
        color: #000;
    }

    .calendar-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .calendar-header {
        text-align: center;
        padding: 20px;
        background-color: #0207ba;
        /* Dark blue */
        color: #ff7900;
    }

    .calendar-header h1 {
        font-size: 3em;
        margin-bottom: 10px;
        color:#ff7900;
    }

    .calendar-header h2 {
        font-size: 2.5em;
        color:#ffffff;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin: 20px 0;
    }

    .event {
        padding: 15px;
        border-radius: 10px;
        text-align: center;
        font-size: 1.1em;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 120px;
    }

    .orange {
        background-color: #FFA500;
    }

    /* Orange */
    .blue {
        background-color: #0207ba;
    }

    /* Blue */
    .red {
        background-color: #FF0000;
    }

    /* Red */

    .calendar-footer {
        text-align: center;
        padding: 20px;
        background-color: #0207ba;
        /* Dark blue */
        color: #fff;
        margin-top: 20px;
    }

    .calendar-footer h3 {
        font-size: 1.9em;
        margin-bottom: 10px;
        color:#ff7900;
    }

    .calendar-footer h2 {
        font-size: 1.5em;
        margin-bottom: 10px;
        color:#fff000;
    }

    .calendar-footer p {
        font-size: 1.5em;
        line-height: 1.5;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .calendar-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .event {
            min-height: 100px;
            font-size: 0.8em;
        }

        .calendar-header h1 {
            font-size: 1.5em;
        }

        .calendar-header h2 {
            font-size: 1.2em;
        }
    }

    @media (max-width: 480px) {
        .calendar-grid {
            grid-template-columns: 1fr;
        }

        .event {
            min-height: 80px;
            font-size: 0.7em;
        }

        .calendar-header {
            padding: 10px;
        }

        .calendar-header h1 {
            font-size: 1.2em;
        }

        .calendar-header h2 {
            font-size: 1em;
        }

        .calendar-footer {
            padding: 10px;
        }

        .calendar-footer h3 {
            font-size: 1.2em;
        }

        .calendar-footer h2 {
            font-size: 1.6em;
        }

        .calendar-footer p {
            font-size: 0.9em;
        }
    }
</style>
</head>

<body>
    <div class="calendar-container">
        <header class="calendar-header">
            <h1>MUSTCU CALENDAR OF EVENTS</h1>
            <h2>JANUARY - MAY SEMESTER 2025</h2>
        </header>

        <div class="calendar-grid">
            <!-- January Events -->
            <div class="event orange">19th JAN SUN<br>THEME EXPOSITION<br>Leonard Karanja</div>
            <div class="event blue">24th JAN FRI<br>FREEDOM IN CONTENTMENT<br>Rev. Carolyne Mwangi</div>
            <div class="event orange">26th JAN SUN<br>TEACH ME HOW TO PRAY<br>Paul Kariuki</div>
            <div class="event red">31st JAN FRI<br>INTIMACY WITH GOD<br>Pst. Martin Obura</div>

            <!-- February Events -->
            <div class="event blue">2nd FEB SUN<br>EVANGELISM WEEK<br>Dr. Jennifer Gitie</div>
            <div class="event orange">7th FEB FRI<br>FAMILY OF CHRIST<br>Pst. Miriam Nkanata</div>
            <div class="event blue">9th FEB SUN<br>MEDIA CHOICE<br>Steve Waithaka</div>
            <div class="event orange">14th FEB FRI<br>FLEE AND BE FREE<br>Caroline Kasaya</div>
            <div class="event red">15th FEB SAT<br>PURE THOUGHTS PURE ACTIONS<br>Chastity Dinner<br>Caroline Kasaya
            </div>
            <div class="event blue">16th FEB SUN<br>BIBLE STUDY LAUNCH<br>Sisters sunday / CBR Week<br>Nelius Nyambura
            </div>
            <div class="event orange">21st FEB FRI<br>EXPOSITION OF 1 THESSALONIANS<br>Bill Mukabwa</div>
            <div class="event blue">23rd FEB SUN<br>POST MODERNISM<br>Ben Birgen</div>
            <div class="event orange">26th FEB FRI<br>SCARRED NOT SCORCHED<br>Sofia Tsenga</div>
            <div class="event red">1st MAR SAT<br>SPORTS DAY</div>
            <div class="event blue">2nd MAR SUN<br>TRINITY EXPLORED<br>Kenneth Arinaitwe</div>

            <!-- March Events -->
            <div class="event orange">7th MAR FRI<br>TRANSACTIONAL FAITH<br>Steve Okoth</div>
            <div class="event blue">9th MAR SUN<br>COST OF DISCIPLESHIP<br>High school sunday<br>Julius Mugambi</div>
            <div class="event orange">14th MAR FRI<br>SERVANTHOOD<br>Hospitality Night</div>
            <div class="event red">15th MAR SAT<br>PRAYER POINTS BONDING</div>
            <div class="event blue">16th MAR SUN<br>HAND THAT GIVETH<br>Brothers Sunday<br>Dr. Githu Wachira</div>
            <div class="event red">21st MAR FRI<br>IN YOUR PRESENCE<br>Worship Kesha<br>Edward Kithi</div>
            <div class="event blue">23rd MAR SUN<br>YOUTH AND POLITICS<br>Dr. Pamela Odhiambo</div>
            <div class="event orange">28th MAR FRI<br>DISCIPLESHIP FOR MISSIONS<br>First years ministration<br>Mwaki
                Shakhe</div>
            <div class="event red">29th MAR SAT<br>MISSIONS TRAINING</div>

            <!-- April Events -->
            <div class="event blue">30th MAR SUN<br>UNITY IN DIVERSITY<br>African Sunday<br>Andrew Muganda</div>
            <div class="event orange">4th APR FRI<br>CHRISTIAN RESPONSE TO LGBTQ<br>Dr. Anthony Mbuki</div>
            <div class="event blue">6th APR SUN<br>NEHEMIAH<br>Jennifer Munge</div>
            <div class="event orange">11th APR FRI<br>SALT AND LIGHT<br>Emmanuel Chome</div>
            <div class="event blue">13th APR SUN<br>FIRM IN FAITH<br>Maithya Muthama</div>
            <div class="event orange">18th APR FRI<br>KWA AJILI YA UPENDO<br>Holy Communion<br>Urita Ntinyari</div>
            <div class="event red">19th APR SAT<br>ASSOCIATES WEEKEND</div>
            <div class="event blue">20th APR SUN<br>THAT I MAY LIVE<br>Missions Support<br>Collins Too</div>
            <div class="event orange">25th APR FRI<br>EBENEZA<br>Elders Night<br>Njoroge Njuguna</div>
            <div class="event blue">27th APR SUN<br>EXPOSITION OF PSALMS 121<br>Pst. Timothy Nyamgero</div>

            <!-- May Events -->
            <div class="event orange">2nd MAY FRI<br>AGAINST THE WALL<br>Timothy Destiny</div>
            <div class="event blue">4th MAY SUN<br>IMAGE OF CHRIST<br>Rev. Lucas Owako</div>
            <div class="event orange">9th MAY FRI<br>HERE I AM<br>Daniel Kyalo</div>
            <div class="event red">SPECIAL<br>EVENTS</div>
            <div class="event blue">SUNDAY<br>SERVICES</div>
            <div class="event orange">FRIDAY<br>FELLOWSHIP</div>
        </div>

        <footer class="calendar-footer">
            <h3>SEMESTER THEME</h3>
            <h2>Called to Holiness</h2>
            <h3>2 TIMOTHY 2:22</h3>
            <p>Flee the evil desires of youth and pursue righteousness, faith, love and peace, along with those who call
                on the Lord out of a pure heart.</p>
        </footer>
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
                                <li><a href="index-slider.php">home</a></li>
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
    </div>
</body>

</html>