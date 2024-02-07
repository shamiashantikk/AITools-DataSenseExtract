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
            <img id="img" src="{{ asset('sample/bang.jpg') }}" />
        </div>
    </div>
    <script>
        window.onload = function() {
    var img = document.getElementById('img');
    var eyeDetected = false;
    var eyeRects = [];

    var eyeTracker = new tracking.ObjectTracker('eye');
    eyeTracker.setStepSize(0.9);
    eyeTracker.setEdgesDensity(0.1);

    tracking.track('#img', eyeTracker);

    img.onload = function() {
        if (eyeDetected) {
            var intensityThreshold = 200;
            var redChannelThreshold = 200;
            var brightnessThreshold = 100;

            eyeRects.forEach(function(eyeRect) {
                console.log('Detected eye at:', eyeRect.x, eyeRect.y);
                var glareDetected = detectGlare(img, eyeRect, intensityThreshold, redChannelThreshold, brightnessThreshold);
                
                if (glareDetected) {
                    console.log('Glare detected in the eye region.');
                } else {
                    console.log('No glare detected in the eye region.');
                }
                //window.plot(eyeRect.x, eyeRect.y, eyeRect.width, eyeRect.height, 'eye');
            });
        }
        };

        eyeTracker.on('track', function(event) {
            if (event.data.length > 0) {
                eyeDetected = true;
                eyeRects = event.data;
                img.src = img.src; // Trigger the onload event
            } else {
                eyeDetected = false;
            }
        });

            function detectGlare(img, eyeRect, intensityThreshold, redChannelThreshold, brightnessThreshold) {
                // Create a canvas and draw the face region on it
                var canvas = document.createElement('canvas');
                canvas.width = eyeRect.width;
                canvas.height = eyeRect.height;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, eyeRect.x, eyeRect.y, eyeRect.width, eyeRect.height, 0, 0, eyeRect.width, eyeRect.height);

                // Get the pixel data of the face region
                var imageData = ctx.getImageData(0, 0, eyeRect.width, eyeRect.height);
                var data = imageData.data;

                // Simple brightness check (adjust as needed)
                var brightnessSum = 0;
                for (var i = 0; i < data.length; i += 4) {
                    var brightness = (data[i] + data[i + 1] + data[i + 2]) / 3;
                    brightnessSum += brightness;
                }

                var averageBrightness = brightnessSum / (data.length / 4);

                // Compare average brightness to the threshold
                return averageBrightness > brightnessThreshold;
            }


            function detectGlares(img, eyeRect, intensityThreshold, redChannelThreshold,brightnessThreshold ) {
                var faceCanvas = document.createElement('canvas');
                var faceContext = faceCanvas.getContext('2d');
                faceCanvas.width = eyeRect.width;
                faceCanvas.height = eyeRect.height;
                faceContext.drawImage(img, eyeRect.x, eyeRect.y, eyeRect.width, eyeRect.height, 0, 0, eyeRect.width, eyeRect.height);

                // Get pixel data from the face region
                var imageData = faceContext.getImageData(0, 0, eyeRect.width, eyeRect.height);
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
                return false; // no glare detected
            }

            function cropImage(image, rect) {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                canvas.width = rect.width;
                canvas.height = rect.height;
                ctx.drawImage(image, rect.x, rect.y, rect.width, rect.height, 0, 0, rect.width, rect.height);
                var croppedImage = new Image();
                croppedImage.src = canvas.toDataURL();
                return croppedImage;
            }

            //step 1: convert image to grayscale
            function convertToGrayscale(img) {
                var canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, img.width, img.height);

                // Get the image data
                var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

                // Convert the image data to grayscale
                var data = imageData.data;
                for (var i = 0; i < data.length; i += 4) {
                    var grayscale = 0.3 * data[i] + 0.59 * data[i + 1] + 0.11 * data[i + 2];
                    data[i] = grayscale;
                    data[i + 1] = grayscale;
                    data[i + 2] = grayscale;
                }

                ctx.putImageData(imageData, 0, 0);
                return canvas;
            }

            //step2: threshold kan image
            function thresholdImage(image, threshold) {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                canvas.width = image.width;
                canvas.height = image.height;
                ctx.drawImage(image, 0, 0, image.width, image.height);
                var imageData = ctx.getImageData(0, 0, image.width, image.height);
                
                for (var i = 0; i < imageData.data.length; i += 4) {
                    var average = (imageData.data[i] + imageData.data[i + 1] + imageData.data[i + 2]) / 3;
                    var binaryValue = average > threshold ? 255 : 0;
                    imageData.data[i] = binaryValue;
                    imageData.data[i + 1] = binaryValue;
                    imageData.data[i + 2] = binaryValue;
                }
                
                ctx.putImageData(imageData, 0, 0);
                
                var thresholdedImage = new Image();
                thresholdedImage.src = canvas.toDataURL();
                return thresholdedImage;
            }

            //step3: count the number to nonzero
            function countNonzeros(image) {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                canvas.width = image.width;
                canvas.height = image.height;
                ctx.drawImage(image, 0, 0, image.width, image.height);
                var imageData = ctx.getImageData(0, 0, image.width, image.height);
                
                var nonZeroCount = 0;
                //step 4: If the count is larger than say 1% of the image size then the image should be classified as glared.
                for (var i = 0; i < imageData.data.length; i += 4) {
                    var binaryValue = imageData.data[i];
                    if (binaryValue > 0) {
                        nonZeroCount++;
                    }
                }
                
                return nonZeroCount;
            }
        };
    </script>
</body>
</html>
