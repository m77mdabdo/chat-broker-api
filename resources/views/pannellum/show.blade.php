<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>360-degree Image Viewer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
        }

        #panorama {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <div id="panorama"></div>

    <script src="https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.js"></script>

    <script>
        window.addEventListener('DOMContentLoaded', function() {
            pannellum.viewer('panorama', {
                "type": "equirectangular",
                "panorama": "{{ $imageUrl }}",
                "autoLoad": true
                // Replace with your actual image URL
                // Add more configuration options as needed
            });
        });
    </script>
</body>

</html>
