<!-- resources/views/eye-detection.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Face and Eye Detection</title>
    <link rel="stylesheet" href="{{ mix('resources/css/demo.css') }}">
    <script src="{{ mix('resources/js/tracking/tracking-min.js') }}" defer></script>
    <script src="{{ mix('resources/js/tracking/data/eye-min.js') }}" defer></script>
    <script src="{{ mix('resources/js/tracking/data/face-min.js') }}" defer></script>
    <style>
        .rect {
            border: 2px solid #a64ceb;
            position: absolute;
        }
        .face {
            border: 2px solid #4caf50; /* Green border for face */
        }
        .eye {
            border: 2px solid #2196f3; /* Blue border for eye */
        }
        .glare {
            border: 2px solid #ff0000; /* Red border for glare */
        }
        #img {
            position: absolute;
            top: 50%;
            left: 50%;
            margin: -173px 0 0 -300px;
        }
    </style>
</head>
<body>
    <div class="demo-title">
        <p><a href="http://trackingjs.com" target="_parent">tracking.js</a> - detect face and eyes in an image</p>
    </div>
    <div class="demo-frame">
        <div class="demo-container">
            <img id="img" src="{{ asset('sample/kelni.jpg') }}" />
        </div>
    </div>
    <script>
        window.onload = function() {
            var img = document.getElementById('img');

            //0.9, 1.0, 0.2 @ 1.0, 1.0, 0.2
            var faceTracker = new tracking.ObjectTracker('face');
            faceTracker.setStepSize(1.0);
            faceTracker.setInitialScale(1.0);
            faceTracker.setEdgesDensity(0.2);

            var eyeTracker = new tracking.ObjectTracker('eye');
            eyeTracker.setStepSize(1.0);
            eyeTracker.setInitialScale(1.0);
            eyeTracker.setEdgesDensity(0.2);

            tracking.track('#img', faceTracker);
            tracking.track('#img', eyeTracker);

            var faceDetected = false; // Flag to track if a face is detected

            faceTracker.on('track', function(event) {
                if (event.data.length > 0) {
                    // Face detected
                    faceDetected = true;
                    var faceRect = event.data[0];
                    console.log('Detected face at:', faceRect.x, faceRect.y);
                    window.plot(faceRect.x, faceRect.y, faceRect.width, faceRect.height, 'face');
                } 
            });

            eyeTracker.on('track', function(event) {
                event.data.forEach(function(eyeRect) {
                    if (faceDetected) {
                        // Draw eyes only if a face is detected
                        console.log('Detected eye at:', eyeRect.x, eyeRect.y);
                        window.plot(eyeRect.x, eyeRect.y, eyeRect.width, eyeRect.height, 'eye');

                        // Check for glare in the eye region (example algorithm)
                        var intensityThreshold = 100; // Adjust as needed
                        var redChannelThreshold = 200; // Adjust as needed
                        var brightnessThreshold = 200; // Adjust as needed
                        var glareDetected = detectGlare(img, eyeRect, intensityThreshold, redChannelThreshold);
                        if (glareDetected) {
                            console.log('Glare detected in the eye region.');
                            window.plot(eyeRect.x, eyeRect.y, eyeRect.width, eyeRect.height, 'glare');
                        }else{
                        console.log('No glare detected!');
                        }
                    }
                });
            });

            window.plot = function(x, y, w, h, type) {
                var rect = document.createElement('div');
                document.querySelector('.demo-container').appendChild(rect);
                rect.classList.add('rect');
                rect.classList.add(type);
                rect.style.width = w + 'px';
                rect.style.height = h + 'px';
                rect.style.left = (img.offsetLeft + x) + 'px';
                rect.style.top = (img.offsetTop + y) + 'px';
            };

            function detectGlare(img, eyeRect, intensityThreshold, redChannelThreshold,brightnessThreshold ) {
                var eyeCanvas = document.createElement('canvas');
                var eyeContext = eyeCanvas.getContext('2d');
                eyeCanvas.width = eyeRect.width;
                eyeCanvas.height = eyeRect.height;
                eyeContext.drawImage(img, eyeRect.x, eyeRect.y, eyeRect.width, eyeRect.height, 0, 0, eyeRect.width, eyeRect.height);

                // Get pixel data from the face region
                var imageData = eyeContext.getImageData(0, 0, eyeRect.width, eyeRect.height);
                var data = imageData.data;

                // Check each pixel for high intensity in the red channel
                for (var i = 0; i < data.length; i += 4) {
                    var r = data[i];
                    var g = data[i + 1];
                    var b = data[i + 2];

                    // Calculate brightness
                    var brightness = (r + g + b) / 3;

                    // Check if the brightness is higher than the threshold
                    if (brightness > brightnessThreshold) {
                        // Check if there is a significant difference in color channels
                        var channelDifference = Math.max(r, g, b) - Math.min(r, g, b);
                        if (channelDifference > channelDifferenceThreshold) {
                            // Found a pixel with high brightness and color channel differences, consider it as glare
                            return true;
                        }
                    }
                }
                // No glare detected
                return false;
            }
        };
    </script>
</body>
</html>