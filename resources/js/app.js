function ekUpload() {
    var fileToUpload;
  
    function Init() {
        var fileSelect = document.getElementById('file-upload');
        fileDrag = document.getElementById('file-drag');
  
        fileSelect.addEventListener('change', fileSelectHandler, false);
  
        // Is XHR2 available?
        var xhr = new XMLHttpRequest();
        if (xhr.upload) {
            // File Drop
            fileDrag.addEventListener('dragover', fileDragHover, false);
            fileDrag.addEventListener('dragleave', fileDragHover, false);
            fileDrag.addEventListener('drop', fileSelectHandler, false);
        }
    }
  
    function fileDragHover(e) {
        var fileDrag = document.getElementById('file-drag');
        e.stopPropagation();
        e.preventDefault();
        fileDrag.className = (e.type === 'dragover' ? 'hover' : 'modal-body file-upload');
    }
  
    function fileSelectHandler(e) {
        // Fetch FileList object
        var files = e.target.files || e.dataTransfer.files;
        // Cancel event and hover styling
        fileDragHover(e);
  
        // Process all File objects
        for (var i = 0, f; f = files[i]; i++) {
            parseFile(f);
            fileToUpload = f;
        }
    }
  
    function uploadFile() {
        var xhr = new XMLHttpRequest(),
            formData = new FormData();
        if (!fileToUpload) {
            console.error('No file to upload.');
            return;
        }
        // Append the file to the FormData object
        formData.append('fileUpload', fileToUpload);
  
        // Include the CSRF token in the FormData object
        var token = document.head.querySelector('meta[name="csrf-token"]');
        formData.append('_token', token.content);
  
        // Specify the URL for the file upload route
        var uploadUrl = '/upload'; 
  
        // Start the upload
        xhr.open('POST', uploadUrl, true);
  
        // Handle the response from the server
        xhr.onload = function () {
          if (xhr.status === 200) {
              var response = JSON.parse(xhr.responseText);
              console.log(response);
              
              if (response.success) {
                var blueBackgroundMessage = response.isBlueBackground ? 'Yes' : 'No';
                var humanMessage = response.isHuman ? 'Yes' : 'No';
                var faceMessage = ''; 
                var eyeMessage = '';
                var glareMessage = '';

                setupFaceAndEyeDetection(document.getElementById('file-image'), function (result) {
                    var faceMessage = result.faceDetected ? 'Yes' : 'No';
                    var eyeMessage = result.eyeDetected ? 'Yes' : 'No';
                    var glareMessage = result.glareDetected ? 'Yes' : 'No';
                    
                    // Update the text content of the result elements
                    document.getElementById('face-result').textContent = faceMessage;
                    document.getElementById('eye-result').textContent = eyeMessage;
                    document.getElementById('glare-result').textContent = glareMessage;
                
                    // Concatenate all messages together
                    var message = 'Blue Background: ' + blueBackgroundMessage + '<br>' +
                        'Human Detected: ' + humanMessage + '<br>' +
                        'Face Detected: ' + faceMessage + '<br>' +
                        'Eyes Detected: ' + eyeMessage + '<br>' +
                        'Glare Detected: ' + glareMessage;
                
                    // Display the upload result
                    displayUploadResult(message, 'success');
                
                    // Show or hide the upload result section based on detection results
                    var uploadResultSection = document.getElementById('upload-result-section');
                    if (result.faceDetected && result.eyeDetected && !result.glareDetected) {
                        uploadResultSection.classList.remove('hidden');
                    } else {
                        uploadResultSection.classList.add('hidden');
                    }
                
                    // Show alert based on detection results
                    if (response.isBlueBackground && response.isHuman) {
                        showAlert('Image uploaded successfully!', 'success');
                    } else {
                        showAlert('Image cannot be uploaded. Requirements not fulfilled.', 'error');
                    }
                    var uploadResultSection = document.getElementById('upload-result-section');
                    uploadResultSection.classList.remove('hidden');
                });
              } else {
                  displayUploadResult(response.message, 'error');
              }
          } else {
              console.error('File upload failed. Status:', xhr.status);
          }
        };
        // Send the FormData object containing the file
        xhr.send(formData);
    }
  
    function displayUploadResult(message, messageType) {
      var resultBox = document.getElementById('upload-result');
  
      if (resultBox) {
          // Update the content of the result box with the specified message
          resultBox.innerHTML = message;
  
          // Remove any existing classes
          resultBox.classList.remove('hidden', 'alert', 'alert-success', 'alert-danger');
  
          // Determine the appropriate class based on the messageType (optional)
          if (messageType === 'success') {
              resultBox.classList.add('alert', 'alert-success');
          } else if (messageType === 'error') {
              resultBox.classList.add('alert', 'alert-danger');
          }
  
          // Show the result box
          resultBox.classList.remove('hidden');
      } else {
          console.error("Element with ID 'upload-result' not found.");
      }
    }
  
    function showAlert(message, messageType = 'success') {
      var messageBox = document.getElementById('message-box');
    
      // Update the content of the message box with the specified message
      messageBox.textContent = message;
    
      // Remove any existing classes
      messageBox.classList.remove('hidden', 'alert', 'alert-success', 'alert-danger');
    
      // Determine the appropriate class based on the messageType
      if (messageType === 'success') {
        messageBox.classList.add('alert', 'alert-success');
      } else if (messageType === 'error') {
        messageBox.classList.add('alert', 'alert-danger');
      }
    
      // Show the message box
      messageBox.classList.remove('hidden');
    }  
  
    function parseFile(file) {
      var imageName = file.name;
      var isGood = (/\.(?=gif|jpg|png|jpeg)/gi).test(imageName);
  
      if (isGood) {
          // Hide elements related to the previous image
          document.getElementById('start').classList.add("hidden");
          document.getElementById('response').classList.remove("hidden");
          document.getElementById('notimage').classList.add("hidden");
  
          // Thumbnail Preview
          document.getElementById('file-image').classList.remove("hidden");
          document.getElementById('file-image').src = URL.createObjectURL(file);
  
          // Remove face detection boxes
          var faceBoxes = document.querySelectorAll('.rect');
          faceBoxes.forEach(function(box) {
              box.remove();
          });
      } else {
          document.getElementById('file-image').classList.add("hidden");
          document.getElementById('notimage').classList.remove("hidden");
          document.getElementById('start').classList.remove("hidden");
          document.getElementById('response').classList.add("hidden");
      }
    }
  
    function setupFaceAndEyeDetection(img, callback) {
      //var img = document.getElementById('file-image');
      var faceDetected = false; // Flag to track if a face is detected
      var eyeDetected = false; // Flag to track if an eye is detected
      var eyePositions = []; // Array to store eye positions
      var glareDetected = false; // Flag to track glare detection
  
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
            var faceMessage = 'Face Detected at: ' + faceRect.x + ' ' + faceRect.y;
            //console.log('Detected face at:', faceRect.x, faceRect.y);
            window.plot(faceRect.x, faceRect.y, faceRect.width, faceRect.height, 'face');
            //displayDetectionMessage('Face Detection', 'Yes', faceMessage);
        } else {
            //displayDetectionMessage('Face Detection', 'No');
        }
    });

    eyeTracker.on('track', function(event) {
        event.data.forEach(function(eyeRect) {
            if (faceDetected) {
                // Draw eyes only if a face is detected
                eyeDetected = true;
                //console.log('Detected eye at:', eyeRect.x, eyeRect.y);
                window.plot(eyeRect.x, eyeRect.y, eyeRect.width, eyeRect.height, 'eye');
                eyePositions.push(eyeRect.x + ' ' + eyeRect.y);
                // Check for glare in the eye region (example algorithm)
                var intensityThreshold = 100; // Adjust as needed
                var redChannelThreshold = 200; // Adjust as needed
                var brightnessThreshold = 130; // Adjust as needed
                var glareDetected = detectGlare(img, eyeRect, brightnessThreshold);                
            }
            
        });
        callback({
            faceDetected: faceDetected,
            eyeDetected: eyeDetected,
            glareDetected: glareDetected
        });        
    });

    function displayDetectionMessage(detectionType, detectionResult, additionalInfo = '') {
        var messageBox = document.getElementById('upload-result');
        var message = detectionType + ': ' + detectionResult;
        if (additionalInfo !== '') {
            message += '<br>' + additionalInfo;
        }
    
        // Check if the message box is currently hidden
        var isMessageBoxHidden = messageBox.classList.contains('hidden');
    
        // Create a new div element to contain the message
        var messageDiv = document.createElement('div');
        messageDiv.innerHTML = message;
    
        // Append the new message div to the existing content of the message box
        messageBox.appendChild(messageDiv);
        
        // If the message box was hidden, display it now
        if (isMessageBoxHidden) {
            messageBox.classList.remove('hidden');
        }
    }
    
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
      
    }
  
    // Attach the uploadFile function to the submit button click event
    document.getElementById('submit-button').addEventListener('click', function (e) {
        e.preventDefault();
        uploadFile();
        //setupFaceAndEyeDetection();
    });
  
    // Check for the various File API support.
    if (window.File && window.FileList && window.FileReader) {
        Init();
    } else {
        document.getElementById('file-drag').style.display = 'none';
    }
  }
  
  ekUpload();
  