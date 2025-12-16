<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Meru University Christian Union</title>
    <!-- Favicon Icon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/resized_image_1.jpg">
    <style>
        /* Overlay styling */
        .popup-overlay {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Popup content styling */
        .popup-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        /* Close button styling */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            color: #333;
            cursor: pointer;
            border: none;
            background: none;
        }

        /* Button styling */
        .popup-btn {
            display: inline-block;
            margin: 10px 5px;
            padding: 10px 20px;
            background-color: #0207ba;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .popup-btn:hover {
            background-color: #ff7900;
        }

        /* Responsive adjustments */
        @media (max-width: 500px) {
            .popup-content {
                width: 85%;
            }
        }
    </style>
</head>
<body>
    <!-- Pop-up structure -->
    <div id="membershipPopup" class="popup-overlay">
        <div class="popup-content">
            <button class="close-btn" onclick="closePopup()">×</button>
            <h2>Dear  Members,</h2>
            <p>Greetings in the name of our Lord and Savior! We warmly invite you to register as a member or a Leader of our Christian Union—a family united in faith, love, and service.</p>
            <a href="form 4.php" class="popup-btn">Register Today</a>
            <a href="about.php" class="popup-btn">Learn More</a>
        </div>
    </div>

    <script>
        // Show the pop-up when the page loads
        window.onload = function() {
            document.getElementById('membershipPopup').style.display = 'flex';
        };

        // Function to close the pop-up
        function closePopup() {
            document.getElementById('membershipPopup').style.display = 'none';
        }
    </script>
</body>
</html>