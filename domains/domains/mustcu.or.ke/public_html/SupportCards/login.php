<?php


// Database connection
$host = "localhost";
$dbname = "uvwehfds_Mustcumembersregitration2025sem2";
$username = "uvwehfds_Mustcumembersregitration2025sem2";
$password = "ZYubywsRwefMnsvdXJMs";

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$error = "";
$login_success = false;

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    if (empty($email) || empty($phone)) {
        $error = "Both email and phone number are required.";
    } else {
        // Check if email and phone exist in either table
        $sql = "
            SELECT id, name, email, phone, 'member' AS role FROM members WHERE email = ? AND phone = ?
            UNION
            SELECT id, name, email, phone, 'leader' AS role FROM leaders WHERE email = ? AND phone = ?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $phone, $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Store user details in session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_role"] = $user["role"];
            
            $login_success = true;
        } else {
            $error = "Invalid email or phone number. Please register.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <link rel="shortcut icon" type="image/x-icon" href="images/resized_image_1.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to generate card</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 600px;
            text-align: center;
            max-height: 400px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #0207ba;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ff7900;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login to Generate a Support Card</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter registered Email" required>
            <input type="text" name="phone" placeholder="Enter registered Phone Number" required>
           <div style="margin-bottom: 20px;">
    <button type="submit">Login</button>
</div>
<a href="/">
    <button type="button">Back to Home</button>
</a>

        </form>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title">Error</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h4 class="modal-title">Success</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Login successful!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="redirectBtn">Continue to generate card</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function () {
        <?php if (!empty($error)) { ?>
            showErrorModal('<?php echo addslashes($error); ?>');
        <?php } elseif ($login_success) { ?>
            showSuccessModal();
        <?php } ?>
    });

    function showErrorModal(message) {
        $('#errorMessage').text(message);
        $('#errorModal').modal('show');
    }

    function showSuccessModal() {
        $('#successModal').modal('show');
    }

    $('#redirectBtn').on('click', function () {
        window.location.href = 'index.php'; // Adjust redirect URL as needed
    });
    </script>

</body>
</html>
