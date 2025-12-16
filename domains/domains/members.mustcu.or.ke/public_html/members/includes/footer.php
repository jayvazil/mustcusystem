<!-- Footer -->
<footer class="footer" id="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-12 footer-links">
                <h5><i class="bi bi-link-45deg me-2"></i>Quick Links</h5>
                <ul>
                    <li><a href="/members/dashboard" class="footer-link"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                    <li><a href="/members/profile" class="footer-link"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a href="/members/view_posts" class="footer-link"><i class="bi bi-envelope me-2"></i>Notifications</a></li>
                    <li><a href="/members/bestp-info" class="footer-link"><i class="bi bi-book me-2"></i>BEST P</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-12 footer-links">
                <h5><i class="bi bi-folder me-2"></i>Resources</h5>
                <ul>
                    <li><a href="/members/ffc" class="footer-link"><i class="bi bi-book me-2"></i>FFC</a></li>
                    <li><a href="/members/orientation/orientation1" class="footer-link"><i class="bi bi-book me-2"></i>Orientation</a></li>
                    <li><a href="/about" class="footer-link"><i class="bi bi-info-circle me-2"></i>About Us</a></li>
                    <li><a href="/contact" class="footer-link"><i class="bi bi-telephone me-2"></i>Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-12 footer-contact">
                <h5><i class="bi bi-person-lines-fill me-2"></i>Contact Us</h5>
                <p><i class="bi bi-envelope-fill me-2"></i> info@mustcu.or.ke</p>
                <p><i class="bi bi-telephone-fill me-2"></i> +254 123 456 789</p>
                <p><i class="bi bi-geo-alt-fill me-2"></i> Meru, Kenya</p>
            </div>
        </div>
        <div class="footer-verses text-center">
            <h5><i class="bi bi-bookmark-heart me-2"></i>Inspirational Verses</h5>
            <p><i class="bi bi-quote me-2"></i>"Trust in the Lord with all your heart" - Proverbs 3:5</p>
            <p><i class="bi bi-quote me-2"></i>"I can do all things through Christ who strengthens me" - Philippians 4:13</p>
            <p><i class="bi bi-quote me-2"></i>"The Lord is my shepherd; I shall not want" - Psalm 23:1</p>
        </div>
        <div class="footer-logo text-center my-3">
            <img src="https://mustcu.or.ke/images/resized_image_1.jpg" alt="MUST CU Logo" style="max-width: 100px; height: auto;">
        </div>
        <div class="footer-bottom text-center">
            <p>&copy; <?php echo date('Y'); ?> MUST CU. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
    .footer {
        background-color: #ffffff;
        border-top: 1px solid #e0e0e0;
        padding: 30px 0;
        color: #333333;
        position: relative;
        z-index: 1000;
        margin-top: 20px;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.8s ease, transform 0.8s ease;
    }
    .footer.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .footer h5 {
        color: #000000;
        font-weight: bold;
        margin-bottom: 15px;
        font-size: 2.0rem;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .footer a.footer-link {
        color: #0207ba;
        text-decoration: none;
        position: relative;
        transition: color 0.3s ease, transform 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .footer a.footer-link:hover {
        color: #ff7900;
        transform: translateX(5px);
    }
    .footer a.footer-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: #fff000;
        transition: width 0.3s ease;
    }
    .footer a.footer-link:hover::after {
        width: 100%;
    }
    .footer .footer-links ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .footer .footer-links ul li {
        margin-bottom: 20px;
        width: 100%;
        text-align: center;
        font-size: 1.3rem; 
    }
    .footer .footer-contact p {
        margin: 8px 0;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .footer .footer-contact i {
        margin-right: 10px;
        color: #0207ba;
        font-size: 1.2rem;
    }
    .footer .footer-verses {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    .footer .footer-verses h5 {
        text-align: center;
    }
    .footer .footer-verses p {
        margin: 8px 0;
        font-size: 0.95rem;
        color: #333333;
        font-style: italic;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .footer .footer-verses i {
        margin-right: 10px;
        color: #0207ba;
        font-size: 1.1rem;
    }
    .footer .footer-logo img {
        transition: transform 0.3s ease;
    }
    .footer .footer-logo img:hover {
        transform: scale(1.1);
    }
    .footer .footer-bottom {
        border-top: 1px solid #e0e0e0;
        padding-top: 15px;
        margin-top: 25px;
        text-align: center;
        color: #666666;
        font-size: 0.9rem;
    }
    .footer .footer-bottom p {
        margin: 0;
    }
    @media (min-width: 992px) {
        .footer .row {
            display: flex;
            justify-content: space-between;
        }
        .footer .col-lg-4 {
            margin-bottom: 0;
        }
    }
    @media (max-width: 991px) {
        .footer .col-lg-4 {
            margin-bottom: 30px;
        }
        .footer .footer-links ul,
        .footer .footer-contact p {
            justify-content: center;
            text-align: center;
        }
        .footer .footer-links ul li,
        .footer .footer-contact p {
            display: flex;
            justify-content: center;
        }
    }
    @media (max-width: 576px) {
        .footer {
            text-align: center;
            padding: 20px 0;
        }
        .footer h5 {
            font-size: 1.1rem;
        }
        .footer .footer-contact p {
            font-size: 0.9rem;
        }
        .footer .footer-verses p {
            font-size: 0.85rem;
        }
        .footer .footer-bottom {
            font-size: 0.8rem;
        }
        .footer .footer-logo img {
            max-width: 80px;
        }
    }
</style>

<script>
    // Footer fade-in animation on page load
    window.addEventListener('load', function () {
        const footer = document.getElementById('footer');
        if (footer) {
            setTimeout(() => {
                footer.classList.add('visible');
            }, 100); // Slight delay to ensure preloader is handled first
        }
    });
</script>

<!-- WhatsApp Floating Support -->
<div id="whatsapp-support" style="
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    font-family: Arial, sans-serif;
">

    <!-- Welcome message bubble (initially hidden) -->
    <div id="whatsapp-welcome" style="
        background: #0207ba;
        color: white;
        padding: 8px 12px;
        border-radius: 10px;
        margin-bottom: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        font-size: 13px;
        max-width: 200px;
        display: none;
        animation: fadeSlideIn 0.3s ease;
    ">
        Facing any challenge or with any question ?   Click for assistance.
    </div>

    <!-- Main WhatsApp button -->
    <div onclick="toggleWhatsAppPopup()" style="
        background-color: #0207ba;
        color: white;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        font-size: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: relative;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    ">
        <i class="fab fa-whatsapp"></i>
        <!-- Unread badge -->
        <div id="whatsapp-badge" style="
            position: absolute;
            top: -4px;
            right: -4px;
            background: red;
            color: white;
            font-size: 10px;
            padding: 2px 4px;
            border-radius: 8px;
        ">1</div>
    </div>

    <!-- Popup box -->
    <div id="whatsapp-popup" style="
        display: none;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
        background: white;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 10px;
        width: 250px;
        max-width: 90vw;
        margin-bottom: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        position: relative;
    ">
        <!-- Close button -->
        <div onclick="closeWhatsAppPopup()" style="
            position: absolute;
            top: 5px;
            right: 8px;
            cursor: pointer;
            font-weight: bold;
            color: #555;
        ">&times;</div>

        <div style="margin-bottom: 8px; font-weight: bold; color: #ff7900; font-size: 14px;">
            Need help? Please choose an option below:
        </div>
        <div style="margin-bottom: 5px;">
            <a href="https://wa.me/254740714285" target="_blank" style="
                background-color: #0207ba;
                color: white;
                padding: 6px 10px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                width: 100%;
                text-align: center;
                font-size: 14px;
            "> Account Set Up</a>
        </div>
        <div style="margin-bottom: 5px;">
            <a href="https://wa.me/254740714285" target="_blank" style="
                background-color: #0207ba;
                color: white;
                padding: 6px 10px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                width: 100%;
                text-align: center;
                font-size: 14px;
            ">Inquiry</a>
        </div>
        <div>
            <a href="https://wa.me/254740714285" target="_blank" style="
                background-color: #0207ba;
                color: white;
                padding: 6px 10px;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                width: 100%;
                text-align: center;
                font-size: 14px;
            ">Support</a>
        </div>
    </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function toggleWhatsAppPopup() {
    var popup = document.getElementById('whatsapp-popup');
    var welcome = document.getElementById('whatsapp-welcome');
    var badge = document.getElementById('whatsapp-badge');

    if (popup.style.display === 'none' || popup.style.display === '') {
        popup.style.display = 'block';
        setTimeout(() => {
            popup.style.opacity = '1';
            popup.style.transform = 'translateY(0)';
        }, 10);

        // Show welcome message
        welcome.style.display = 'inline-block';

        // Hide badge
        if (badge) badge.style.display = 'none';

        document.addEventListener('click', clickOutsideToClose);
    } else {
        closeWhatsAppPopup();
    }
}

function closeWhatsAppPopup() {
    var popup = document.getElementById('whatsapp-popup');
    var welcome = document.getElementById('whatsapp-welcome');
    popup.style.opacity = '0';
    popup.style.transform = 'translateY(20px)';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 300);

    // Hide welcome message
    welcome.style.display = 'none';

    document.removeEventListener('click', clickOutsideToClose);
}

function clickOutsideToClose(event) {
    var popup = document.getElementById('whatsapp-popup');
    var button = popup.previousElementSibling;
    if (!popup.contains(event.target) && !button.contains(event.target)) {
        closeWhatsAppPopup();
    }
}
</script>
