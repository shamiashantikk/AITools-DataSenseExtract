<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('resources/css/app.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ mix('resources/css/demo.css') }}">
    <title>Image Upload</title>
</head>
<body>
  <h2>File Upload & Image Preview</h2>
  <div class="container">
    <div class="row">
        <div class="col-md-6 mx-auto">
          <form action="{{ route('upload') }}" id="file-upload-form" class="uploader" method="post" enctype="multipart/form-data">
              @csrf
            <input id="file-upload" type="file" name="fileUpload" accept="image/*" />
            <label for="file-upload" id="file-drag">
              <div class="demo-container"><img id="file-image" src="#" alt="Preview" class="hidden"></div>
              <div id="start">
                <i class="fa fa-download" aria-hidden="true"></i>
                <div>Select a file or drag here</div>
                <div id="notimage" class="hidden">Please select an image</div>
                <span id="file-upload-btn" class="btn btn-primary">Select a file</span>
                
              </div>
              <div id="response" class="hidden">
                <div id="messages"></div>
                <div id="file-name" class="hidden"></div>
                <span id="file-upload-btn" class="btn btn-primary">Select a file</span>
                <button class="btn" id="submit-button" type="submit" style="background-color:mediumseagreen;">Upload Image</button>
              </div>
            </label>
          </form>
        </div>
    </div>
    <!-- Result Display Section -->
    @if (isset($isBlueBackground) && isset($isHuman))
        <div class="row mt-4 hidden" id="upload-result-section">
            <div class="col-md-6 mx-auto">
                <h3>Upload Result</h3>
                <div id="upload-result">
                </div>
            </div>
        </div>
    @endif
    <!-- End of Result Display Section -->
    <div class="row mt-4">
      <div class="col-md-5 mx-auto">
        <div id="message-box" class="hidden" role="alert"></div>
      </div>
    </div>
  </div>
  <script src="{{ mix('resources/js/app.js') }}" defer></script>
  <script src="{{ mix('resources/js/tracking/tracking-min.js') }}" defer></script>
  <script src="{{ mix('resources/js/tracking/data/eye-min.js') }}" defer></script>
  <script src="{{ mix('resources/js/tracking/data/face-min.js') }}" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <script>
    window.addEventListener('DOMContentLoaded',function () {
      var img = document.getElementById('file-image');

      //0.9, 1.0, 0.2 @ 1.0, 1.0, 0.2
      var faceTracker = new tracking.ObjectTracker('face');
      faceTracker.setStepSize(1.0);
      faceTracker.setInitialScale(1.0);
      faceTracker.setEdgesDensity(0.2);

      var eyeTracker = new tracking.ObjectTracker('eye');
      eyeTracker.setStepSize(1.0);
      eyeTracker.setInitialScale(1.0);
      eyeTracker.setEdgesDensity(0.2);

      tracking.track('#file-image', faceTracker);
      tracking.track('#file-image', eyeTracker);

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
          //document.getElementById('file-image').value='';
          return false;
      }
    })
  </script>
</body>
</html>
