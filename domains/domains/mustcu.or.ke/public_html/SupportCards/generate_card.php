<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support Card</title>
    <!-- Google Fonts for Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Lobster&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Base Card Styles */
        .support-card {
    position: relative;
    width: 500px;
    background: white;
    border: 5px solid #FF7900;
    outline: 3px dashed #0207ba;
    outline-offset: -8px;
    border-radius: 20px;
    padding: 20px;
    margin: 20px auto;
    font-family: Arial, sans-serif;
    overflow: hidden;
    background: white url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><circle cx="10" cy="10" r="1" fill="%23FFD700" fill-opacity="0.1"/></svg>') repeat;
    animation: fadeIn 1s ease-in;
    margin-top: 150px; /* Adds 50px of space above the card */
}

        /* Header Styles */
        .header {
            text-align: center;
        }

        .header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
        }

        .header h1 {
            font-family: 'Playfair Display', serif;
            color: #FF7900;
            margin: 10px 0;
            font-size: 1.8em;
        }

        .header h3 {
            font-size: 1.2em;
            color: #0207BA;
        }

        /* Decorative Line */
        .decorative-line {
            border: none;
            height: 2px;
            background: linear-gradient(to right, transparent, #0207ba, transparent);
            margin: 10px 0;
        }

        /* Encouragement Text */
        .encourage-text {
            text-align: center;
            font-size: 1.1em;
        }

        .highlight-text {
            color: #FF7900;
            font-weight: bold;
        }

        /* User Info Section */
        .user-info {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        .picture img {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            margin-right: 15px;
        }

        .text p {
            margin: 5px 0;
        }

        .user-name {
            font-family: 'Lobster', cursive;
            color: #0207BA;
            text-shadow: 1px 1px 2px #FFD700;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 0.9em;
            color: #666;
        }

        /* Button Styles */
        button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: linear-gradient(to right, #FF7900, #FFA500);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 1em;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s, background 0.3s;
        }

        button:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            background: linear-gradient(to right, #FF4500, #FF7900);
        }

        /* Floating Shapes */
        .floating-shape {
            position: absolute;
            background: radial-gradient(circle, #0207ba, transparent);
            border-radius: 50%;
           
            
        }

        @keyframes float {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }

        
        
        .corner-bottom-right {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-bottom: 30px solid #FFD700;
            border-left: 30px solid transparent;
        }
        

        /* Fade-in Animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
 

/* Decorative top and bottom shapes */
.support-card::before,
.support-card::after {
    content: "";
    position: absolute;
    width: 120px;
    height: 120px;
    background: rgba(255, 121, 0, 0.2);
    border-radius: 50%;
    z-index: -1;
}

.support-card::before {
    top: -50px;
    left: -50px;
    
}

.support-card::after {
    bottom: -50px;
    right: -50px;
}

/* Header Styling */
.header img {
    width: 90px;
    height: auto;
}

.header h1 {
    font-family: 'Lobster', cursive;
    color: #FF7900;
    margin: 10px 0;
    font-size: 1.8em;
}

.header h3 {
    color: #0207BA;
    font-size: 1.2em;
    margin-bottom: 15px;
}

/* Highlighted Text */
.highlight-text {
    color: #FFFFFF;
    font-weight: 700;
    font-size: 1.2em;
}

/* Encouragement Text */
.encourage-text {
    font-size: 1.2em;
    background: #0207BA;
    color: white;
    padding: 10px;
    border-radius: 10px;
    margin: 15px 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* User Info Section */
.user-info {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin: 20px 0;
}

/* User Picture */
.picture img {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 10px; /* smaller value for slightly rounded corners */
    border: 4px solid #FF7900;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Text Information */
.text p {
    margin: 10px 0;
    text-align: left;
    color: #0207BA;
    font-size: 1.1em;
    font-weight: 500;
}

/* Footer Section */
.footer {
    background: #0207BA;
    color: white;
    padding: 12px;
    border-radius: 10px;
    font-size: 1.1em;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    margin-top: 20px;
}
        .text {
    border: 2px dashed #FFD700;
    padding: 10px;
    border-radius: 10px;
}

/* Button Styling */
.download-container {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

button {
    background: linear-gradient(to right, #FF7900, #FF4500);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.3em;
    transition: transform 0.2s, box-shadow 0.2s;
    width: 100%;
    max-width: 280px;
    text-align: center;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Button Hover Effect */
button:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
}

/* Responsive Design */
@media (max-width: 600px) {
    .support-card {
        width: 90%;
        padding: 15px;
    }
    .user-info {
        flex-direction: column;
    }
    .picture img {
        width: 100px;
        height: 100px;
    }
}


/* Style the button and place it at the bottom */
.download-container {
    width: 100%;
    display: flex;
    justify-content: center;
    position: absolute;
    bottom: 10px;
}

button {
    background: #FF7900;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.5em;
    transition: transform 0.2s;
    width: 100%; /* Make it full width */
    max-width: 300px; /* Limit button width */
    text-align: center;
}

/* Hover effect */
button:hover {
    transform: scale(1.05);
}

        
    </style>
</head>
<body>

<?php
// Define constants (adjust paths and values as needed)
define('INSTITUTION_LOGO', 'IMAGES/MUST CU OFFICIAL LOGO 22.jpg');
define('ORGANIZATION_NAME', 'Meru University Christian Union');
define('MISSION_TITLE', 'Samburu Mission 2025');
define('MISSION_DATES', '10th-18th May 2025');

// Initialize variables
$errors = [];
$error_message = '';
$file_path = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $account_number = trim($_POST['account_number']);
    $name = trim($_POST['name']);
    $amount = trim($_POST['amount']);
    $file = $_FILES['picture'];
    
    // Validation
    if (empty($account_number)) $errors[] = 'Account number is required';
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($amount) || !is_numeric($amount)) $errors[] = 'Amount must be a valid number';

    if (!empty($file['name'])) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload failed with error code: ' . $file['error'];
        } else {
            $target_dir = "uploads/";
            if (!file_exists($target_dir) && !mkdir($target_dir, 0755, true)) {
                $errors[] = 'Failed to create upload directory';
            } else {
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file["tmp_name"]);
                finfo_close($finfo);

                if (!in_array($mime, $allowed_mimes)) {
                    $errors[] = 'Only JPG, PNG, and GIF files are allowed';
                } elseif ($file['size'] > 2 * 1024 * 1024) {
                    $errors[] = 'File size must be less than 2MB';
                } elseif (!getimagesize($file["tmp_name"])) {
                    $errors[] = 'File is not a valid image';
                } else {
                    $file_name = bin2hex(random_bytes(8)) . '_' . basename($file["name"]);
                    $file_path = $target_dir . $file_name;
                    if (!move_uploaded_file($file["tmp_name"], $file_path)) {
                        $errors[] = 'Error uploading file';
                    }
                }
            }
        }
    } else {
        $errors[] = 'Please upload a picture';
    }

    // Display error messages if any
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }

    // If no errors, display the support card
    if (empty($errors)) {
        echo '<div class="support-card">';
        echo '    <div class="corner-top-left"></div>';
        echo '    <div class="corner-bottom-right"></div>';
        echo '    <i class="fas fa-dove" style="position: absolute; top: 10px; left: 10px; color: #FFD700; font-size: 24px;"></i>';
        echo '    <i class="fas fa-dove" style="position: absolute; bottom: 10px; right: 10px; color: #FFD700; font-size: 24px;"></i>';
        echo '    <div class="header">';
        echo '        <img src="' . INSTITUTION_LOGO . '" alt="Institution Logo">';
        echo '        <h3>' . ORGANIZATION_NAME . '</h3>';
        echo '        <h1>' . MISSION_TITLE . '</h1>';
        echo '    </div>';
        echo '    <hr class="decorative-line">';
        echo '    <p class="encourage-text"><span class="highlight-text">I WILL BE ATTENDING THE MISSION</span></p>';
        echo '    <p>The Samburu Mission will be happening between <strong>' . MISSION_DATES . '</strong>.</p>';
        echo '    <p>I am seeking people to help me raise the registration amount to enable me to go and spread the Gospel in Samburu during the mission.</p>';
        echo '    <div class="user-info">';
        echo '        <div class="picture">';
        echo '            <img src="' . htmlspecialchars($file_path) . '" alt="User Picture">';
        echo '        </div>';
        echo '        <div class="text">';
        echo '            <p><strong>Name:</strong> ' . htmlspecialchars($name) . '</p>';
        echo '            <p><strong>Mpesa Number:</strong> ' . htmlspecialchars($account_number) . '</p>';
        echo '            <p><strong>Amount Needed:</strong> Kes ' . htmlspecialchars($amount) . '</p>';
        echo '        </div>';
        echo '    </div>';
        echo '    <hr class="decorative-line">';
        echo '    <div class="footer">';
        echo '        Thank you for your support! May the Lord Bless you';
        echo '    </div>';
        echo '</div>';
        
        echo '<div class="download-container">';
        echo '<button id="download-btn">Download Card</button>';
        echo '<button id="home-btn" onclick="goHome()">Back to Home</button>';
        echo '</div>';

        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>';
        echo '<script>
            document.getElementById("download-btn").addEventListener("click", function() {
                let card = document.querySelector(".support-card");
                
                // Fix for blurry images - use device pixel ratio and additional options
                html2canvas(card, {
                    scale: window.devicePixelRatio || 2, // Use device pixel ratio for sharp images
                    useCORS: true,                       // Handle cross-origin images
                    allowTaint: true,                    // Allow processing of tainted canvas
                    backgroundColor: null,               // Transparent background
                    logging: false,                      // Disable logging
                    imageTimeout: 0,                     // No timeout for images
                    onrendered: function(canvas) {       // For older versions compatibility
                        let image = canvas.toDataURL("image/png");
                        let link = document.createElement("a");
                        link.href = image;
                        link.download = "Support_Card.png";
                        link.click();
                    }
                }).then(canvas => {
                    let image = canvas.toDataURL("image/png", 1.0); // 1.0 is for max quality
                    let link = document.createElement("a");
                    link.href = image;
                    link.download = "Support_Card.png";
                    link.click();
                });
            });

            function goHome() {
                window.location.href = "/"; // Change to your actual home page URL
            }
        </script>';
    }
} else {
    // Show form if not a POST request
    // You can add your form HTML here if needed
}
?>
<h3>Kindly Download the Card Twice or thrice for a quality card<h3>
</body>

</html>