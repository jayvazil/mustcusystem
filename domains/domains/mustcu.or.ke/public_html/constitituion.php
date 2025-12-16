<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST CU CONSTITUTION</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/resized_image_1.jpg">





    <style>
        .button {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .center-button {
            display: flex;
            padding: 20px 15px;
            background-color: #0207ba;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 10px;
            transition: all 0.3s ease;
            /* Smooth transition for hover effects */
        }

        .center-button:hover {
            background-color: #ff7900;
            transform: translateY(-2px);
            /* Slight lift effect */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            /* Shadow on hover */
        }

        .center-button:active {
            transform: translateY(0);
            /* Press effect */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }


        /* General styles */
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px #FFFF00;
        }

        /* Controls panel */
        .controls {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .button-group {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #0207BA;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0207BA;
        }

        .btn-secondary {
            background-color: #0207ba;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #ff7900;
        }

        /* PDF viewer container */
        .pdf-container {
            width: 100%;
            height: 8000px;
            border: 1px solid #dee2e6;
            margin-top: 20px;
        }

        /* Page controls */
        .page-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .page-input {
            width: 60px;
            padding: 5px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        /* Print options */
        .print-options {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .option-group {
            margin: 10px 0;
        }

        .option-group label {
            margin-right: 15px;
            cursor: pointer;
        }

        /* Zoom controls */
        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        /* Hide controls when printing */
        @media print {
            .controls {
                display: none;
            }

            .container {
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Controls panel -->
        <div class="controls">
            <h2>MUST CU CONSTITUTION</h2>
            <div class="button">
                <a href="index.php" class="center-button">Back to home</a>
            </div>



            <!-- Action buttons -->
            <div class="button-group">
                <button class="btn btn-primary" onclick="printPDF()">Print</button>
                <button class="btn btn-primary" onclick="downloadPDF()">Download</button>
                <button class="btn btn-secondary" onclick="fullScreen()">Full Screen</button>
            </div>
        </div>

        <!-- PDF viewer -->
        <iframe id="pdfViewer" class="pdf-container"></iframe>
    </div>


    <script>
        // Initialize variables
        let currentPage = 1;
        let totalPages = 0;
        let currentRotation = 0;

        // Load the PDF (replace with your PDF path)
        function loadPDF(pdfPath) {
            const viewer = document.getElementById('pdfViewer');
            viewer.src = pdfPath;
        }

        // Navigation functions
        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                updatePage();
            }
        }

        function nextPage() {
            if (currentPage < totalPages) {
                currentPage++;
                updatePage();
            }
        }

        function updatePage() {
            document.getElementById('pageNumber').value = currentPage;
            // Update PDF viewer to show current page
        }

        // Zoom function
        function changeZoom() {
            const zoom = document.getElementById('zoom').value;
            const viewer = document.getElementById('pdfViewer');
            viewer.style.transform = `scale(${zoom})`;
            viewer.style.transformOrigin = 'top left';
        }

        // Rotation function
        function rotateDocument() {
            currentRotation = (currentRotation + 90) % 360;
            const viewer = document.getElementById('pdfViewer');
            viewer.style.transform = `rotate(${currentRotation}deg)`;
        }

        // Print function
        function printPDF() {
            const printComments = document.getElementById('printComments').checked;
            const printBackground = document.getElementById('printBackground').checked;
            const pageRange = document.getElementById('pageRange').value;

            // Prepare print options
            const printOptions = {
                comments: printComments,
                background: printBackground,
                pageRange: pageRange || 'all'
            };

            // Open print dialog
            window.print();
        }

        // Download function
        function downloadPDF() {
            // Replace with your PDF download logic
            const pdfPath = document.getElementById('pdfViewer').src;
            const link = document.createElement('a');
            link.href = pdfPath;
            link.download = 'document.pdf';
            link.click();
        }

        // Full screen function
        function fullScreen() {
            const viewer = document.getElementById('pdfViewer');
            if (viewer.requestFullscreen) {
                viewer.requestFullscreen();
            } else if (viewer.webkitRequestFullscreen) {
                viewer.webkitRequestFullscreen();
            } else if (viewer.msRequestFullscreen) {
                viewer.msRequestFullscreen();
            }
        }

        // Initialize the viewer with a PDF
        window.onload = function () {
            // Replace 'your-pdf-path.pdf' with the actual path to your PDF
            loadPDF('MUSTCU REVIEWED CONSTITUTION 2022.pdf');
        };
    </script>
</body>
<div class="button">
    <a href="constitituion.php" class="center-button">Back to top</a>
</div>

</html>