<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Registration Form</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="images/resized_image_1.jpg">
    <link href="css/all.css" rel="stylesheet" media="screen">

    <style>
        .slideshow {
            position: relative;
            height: 400px;
            margin-bottom: 40px;
            overflow: hidden;
            opacity: 0.7;
        }

        .slide {
            position: absolute;
            width: 100%;
            height: 100%;
            transition: opacity 2s ease-in-out;
            will-change: opacity;
            opacity: 0;
        }

        .slide.active {
            opacity: 0.7;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slide-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 1;
        }

        .slide-text h1 {
            font-size: 50px;
            font-weight: bold;
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
            color: #ff7900;
            margin: 0;
        }

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 20px #0207ba;
            background: white;
        }

        .form-floating {
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .leader-position {
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            position: relative;
        }

        .remove-position {
            position: absolute;
            right: 1rem;
            top: 1rem;
        }

        .alert {
            display: none;
            margin-top: 1rem;
        }

        .btn-secondary {
            background-color: #ff7900;
            border-color: #ff7900;
        }

        .btn-secondary:hover {
            background-color: #ff7900;
            border-color: #ff7900;
        }

        .bg-primary {
            background-color: #0207ba !important;
        }

        .btn-primary {
            background-color: #0207ba;
            border-color: #0207ba;
        }

        .btn-primary:hover {
            background-color: #0207ba;
            border-color: #0207ba;
        }

        .form-select {
            width: 100%;
            max-width: 100%;
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }

        .form-select option {
            white-space: normal;
            word-wrap: break-word;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 1rem;
                margin: 1rem;
            }

            .form-select {
                font-size: 16px;
                height: auto;
                padding: 0.5rem;
                line-height: 1.2;
            }

            .form-floating>label {
                padding: 1rem 0.75rem;
            }

            .leader-position {
                padding: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .remove-position {
                position: static;
                width: 100%;
                margin-top: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 0.75rem;
                margin: 0.5rem;
            }

            .form-select {
                font-size: 10px;
            }

            .leader-position {
                padding: 0.5rem;
            }
        }

        .contact-form-wrapper {
            padding: 10px 0;
        }

        .contact-form {
            background: #fff;
            border-radius: 10px;
        }

        .contact__title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .button-wrapper {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn--base {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background: #0207ba;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn--base:hover {
            background: #ff7900;
        }

        .alert-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #0207ba;
            color: white;
            border-radius: 10px;
            padding: 20px;
            width: 90%;
            max-width: 400px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.6);
            text-align: center;
            font-family: Arial, sans-serif;
            animation: fadeIn 0.5s ease-in-out;
            z-index: 9999;
        }

        .alert-card.error {
            background-color: #DC3545;
        }

        .alert-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .alert-body {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .alert-close {
            background-color: white;
            color: #007BFF;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .alert-close:hover {
            background-color: #0056b3;
            color: white;
        }

        .alert-card.error .alert-close {
            color: #DC3545;
        }

        .alert-card.error .alert-close:hover {
            background-color: #A71D2A;
            color: white;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .spinner-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            font-size: 18px;
        }

        /* Optional: Add a spinning animation */
        .spinner-content::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="form-container">
        <div>
            <div class="slideshow">
                <div class="slide active">
                    <img src="images/integrity 7.jpg" alt="Slide 1">
                    <div class="slide-text">
                        <h1>Registration Form</h1>
                    </div>
                </div>
                <div class="slide">
                    <img src="images/agm.jpg" alt="Slide 2">
                    <div class="slide-text">
                        <h1>Registration Form</h1>
                    </div>
                </div>
                <div class="slide">
                    <img src="images/agm 4.jpg" alt="Slide 3">
                    <div class="slide-text">
                        <h1>Registration Form</h1>
                    </div>
                </div>
                <div class="slide">
                    <img src="images/choir.jpg" alt="Slide 4">
                    <div class="slide-text">
                        <h1>Registration Form</h1>
                    </div>
                </div>
                <div class="slide">
                    <img src="images/service 4.jpg" alt="Slide 5">
                    <div class="slide-text">
                        <h1>Registration Form</h1>
                    </div>
                </div>
            </div>

            <a href="index.php">
                <button type="button" class="btn btn-primary w-100">Back to Home</button>
            </a>
        </div>

        <div id="spinner" class="spinner" style="display: none;">
            <div class="spinner-content">
                Registering...
            </div>
        </div>

        <!-- Success Message Card -->
        <div id="successMessage" class="alert-card" style="display: none;">
            <div class="alert-header">
                🎉 Registration Successful!
            </div>
            <div class="alert-body">
                You have successfully registered. Please check your email for confirmation. 📩
            </div>
            <button class="alert-close" onclick="closeAlert()">OK</button>
        </div>

        <!-- Error Message Card -->
        <div id="errorMessage" class="alert-card error" style="display: none;">
            <div class="alert-header">
                ⚠️ Registration Failed!
            </div>
            <div class="alert-body" id="errorText">
                You are already registered as a MUSTCU member.
            </div>
            <button class="alert-close" onclick="closeAlert()">OK</button>
        </div>
        <section class="contact-form-wrapper">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="contact-form card p-5 shadow-lg">
                            <h3 class="contact__title text-center mb-4">
                                Welcome to Meru University Christian Union Registration
                            </h3>

                            <form id="selectionForm">
                                <p class="text-center mb-4">Are you a member or a leader?</p>
                                <div class="button-wrapper text-center">
                                    <button type="button" class="btn--base" onclick="showForm('member')">Member</button>
                                    <button type="button" class="btn--base" onclick="showForm('leader')">Leader</button>
                                </div>
                            </form>

                            <!-- Member Form -->
                            <form id="memberForm" method="POST" action="/submit.php" style="display: none;">
                                <h4 class="text-center mb-4">Member Registration</h4>
                                <div class="row gy-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" required pattern="[0-9]{10}" title="Enter a 10-digit phone number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Year of Study</label>
                                        <select name="year" class="form-control" required>
                                            <option value="" disabled selected>Select year</option>
                                            <option value="1">1 year</option>
                                            <option value="2">2 year</option>
                                            <option value="3">3 year</option>
                                            <option value="4">4 year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="margin-top: 25px;">Course</label>
                                        <input type="text" name="course" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Year & Month of Completion</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <select name="completion_year" class="form-control" required>
                                                    <option value="" disabled selected>Year</option>
                                                    <option value="2020">2020</option>
                                                    <option value="2021">2021</option>
                                                    <option value="2022">2022</option>
                                                    <option value="2023">2023</option>
                                                    <option value="2024">2024</option>
                                                    <option value="2025">2025</option>
                                                    <option value="2026">2026</option>
                                                    <option value="2027">2027</option>
                                                    <option value="2028">2028</option>
                                                    <option value="2029">2029</option>
                                                    <option value="2030">2030</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="completion_month" class="form-control" required>
                                                    <option value="" disabled selected>Month</option>
                                                    <option value="01">January</option>
                                                    <option value="02">February</option>
                                                    <option value="03">March</option>
                                                    <option value="04">April</option>
                                                    <option value="05">May</option>
                                                    <option value="06">June</option>
                                                    <option value="07">July</option>
                                                    <option value="08">August</option>
                                                    <option value="09">September</option>
                                                    <option value="10">October</option>
                                                    <option value="11">November</option>
                                                    <option value="12">December</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ministry</label>
                                        <select name="ministry" class="form-control">
                                            <option value="">Select Ministry</option>
                                            <option value="Praise and Worship">Praise and Worship</option>
                                            <option value="IT and Publicity">IT and Publicity</option>
                                            <option value="Ushering">Ushering</option>
                                            <option value="Creative">Creative</option>
                                            <option value="Choir">Choir</option>
                                            <option value="Catering">Catering</option>
                                            <option value="Decor">Decor</option>
                                            <option value="Sunday School">Sunday School</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" id="memberBtn" class="btn--base" style="margin-top: 20px;">Register as Member</button>
                            </form>

                            <!-- Leader Form -->
                            <form id="leaderForm" method="POST" action="/submit.php" style="display: none;">
                                <h4 class="text-center mb-4">Leader Registration</h4>
                                <div class="row gy-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}">

                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" required pattern="[0-9]{10}" title="Enter a 10-digit phone number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Year of Study</label>
                                        <select name="year" class="form-control" required>
                                            <option value="" disabled selected>Select year</option>
                                            <option value="1">1 year</option>
                                            <option value="2">2 year</option>
                                            <option value="3">3 year</option>
                                            <option value="4">4 year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="margin-top: 25px;">Course</label>
                                        <input type="text" name="course" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Year & Month of Completion</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <select name="completion_year" class="form-control" required>
                                                    <option value="" disabled selected>Year</option>
                                                    <option value="2020">2020</option>
                                                    <option value="2021">2021</option>
                                                    <option value="2022">2022</option>
                                                    <option value="2023">2023</option>
                                                    <option value="2024">2024</option>
                                                    <option value="2025">2025</option>
                                                    <option value="2026">2026</option>
                                                    <option value="2027">2027</option>
                                                    <option value="2028">2028</option>
                                                    <option value="2029">2029</option>
                                                    <option value="2030">2030</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="completion_month" class="form-control" required>
                                                    <option value="" disabled selected>Month</option>
                                                    <option value="01">January</option>
                                                    <option value="02">February</option>
                                                    <option value="03">March</option>
                                                    <option value="04">April</option>
                                                    <option value="05">May</option>
                                                    <option value="06">June</option>
                                                    <option value="07">July</option>
                                                    <option value="08">August</option>
                                                    <option value="09">September</option>
                                                    <option value="10">October</option>
                                                    <option value="11">November</option>
                                                    <option value="12">December</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ministry</label>
                                        <select name="ministry" class="form-control">
                                            <option value="">Select Ministry</option>
                                            <option value="Praise and Worship">Praise and Worship</option>
                                            <option value="IT and Publicity">IT and Publicity</option>
                                            <option value="Ushering">Ushering</option>
                                            <option value="Creative">Creative</option>
                                            <option value="Choir">Choir</option>
                                            <option value="Catering">Catering</option>
                                            <option value="Decor">Decor</option>
                                            <option value="Sunday School">Sunday School</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Leader Position</label>
                                        <select name="position" class="form-control" required>
                                            <option value="" disabled selected>Select Position</option>
                                            <option value="Chairperson">Chairperson</option>
                                            <option value="Vice Chairperson">Vice Chairperson</option>
                                            <option value="Secretary">Secretary</option>
                                            <option value="Vice Secretary">Vice Secretary</option>
                                            <option value="General Secretary">General Secretary</option>
                                            <option value="Treasurer">Treasurer</option>
                                            <option value="Coordinator">Coordinator</option>
                                            <option value="Facilitator">Facilitator</option>
                                            <option value="Committee Member">Committee Member</option>
                                            
                                            <option value="Sub-Committee Member">Sub-Committee Member</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Leader Docket</label>
                                        <select name="docket" class="form-control" required>
                                            <option value="" disabled selected>Select Docket</option>
                                            <option value="Executive">Executive</option>
                                            <option value="Arts and Media">Arts and Media</option>
                                            <option value="Creative">Creative</option>
                                            <option value="Music Ministry">Music Ministry</option>
                                            <option value="Sunday School">Sunday School</option>
                                            <option value="Bible Study">Bible Study</option>
                                            <option value="Non-residents">Non-residents</option>
                                            <option value="Hospitality">Hospitality</option>
                                            <option value="Missions and Evangelism">Missions and Evangelism</option>
                                            <option value="Discipleship">Discipleship</option>
                                            <option value="Treasury">Treasury</option>
                                            <option value="Prayer">Prayer</option>
                                            <option value="Best-p">Best-p</option>
                                            <option value="FFC">FFC</option>
                                            <option value="Choir">Choir</option>
                                            <option value="IT and Publicity">IT and Publicity</option>
                                            <option value="Praise and Worship">Praise and Worship</option>
                                            <option value="Instrumentalist">Instrumentalist</option>
                                            <option value="Sports">Sports</option>
                                            <option value="Ushering">Ushering</option>
                                            <option value="Decor">Decor</option>
                                            <option value="Catering">Catering</option>
                                            <option value="Editorial">Editorial</option>
                                            <option value="Advocacy">Advocacy</option>
                                            <option value="Welfare">Welfare</option>
                                            <option value="Edeleafty">Edeleafty</option>
                                            <option value="Vukafty">Vukafty</option>
                                            <option value="Brothers">Brothers</option>
                                            <option value="Sisters">Sisters</option>
                                            <option value="Anzafty">Anzafty</option>
                                            <option value="Outreach">Outreach</option>
                                            <option value="Inreach">Inreach</option>
                                            <option value="High School">High School</option>
                                            <option value="Annual Mission Committee">Annual Mission Committee</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" id="leaderBtn" class="btn--base" style="margin-top: 20px;">Register as Leader</button>
                            </form>
                        </div>
                    </div>
                </div>
        </section>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

        <script>
            function showForm(type) {
                document.getElementById('selectionForm').style.display = 'none';
                document.getElementById('memberForm').style.display = type === 'member' ? 'block' : 'none';
                document.getElementById('leaderForm').style.display = type === 'leader' ? 'block' : 'none';
            }

            function closeAlert() {
                document.getElementById('successMessage').style.display = 'none';
                document.getElementById('errorMessage').style.display = 'none';
            }

            // Handle form submissions
            const forms = [document.getElementById('memberForm'), document.getElementById('leaderForm')];
            forms.forEach(form => {
                form.addEventListener('submit', async function(event) {
                    event.preventDefault();

                    // Prevent multiple submissions
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton.disabled) return;
                    submitButton.disabled = true;

                    // Show spinner
                    const spinner = document.getElementById('spinner');
                    spinner.style.display = 'block';

                    try {
                        const formData = new FormData(this);
                        const response = await fetch('/submit', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        // Hide spinner
                        spinner.style.display = 'none';

                        // Handle response
                        if (result.success) {
                            document.getElementById('successMessage').style.display = 'block';
                            this.reset();
                        } else {
                            const errorMsg = document.getElementById('errorText');
                            errorMsg.textContent = result.message || 'An error occurred during registration';
                            document.getElementById('errorMessage').style.display = 'block';
                        }
                    } catch (error) {
                        spinner.style.display = 'none';
                        const errorMsg = document.getElementById('errorText');
                        errorMsg.textContent = 'Network error. Please try again later.';
                        document.getElementById('errorMessage').style.display = 'block';
                        console.error('Error:', error);
                    } finally {
                        submitButton.disabled = false; // Re-enable submit button
                    }
                });
            });

            // Slideshow functionality (if you want to keep it)
            const slides = document.querySelectorAll('.slide');
            let currentSlide = 0;

            function nextSlide() {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }

            setInterval(nextSlide, 5000); // Change slide every 5 seconds
        </script>
    </div>
</body>

</html>