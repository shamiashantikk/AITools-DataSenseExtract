// File Upload
function ekUpload() {
  var fileToUpload;
  function Init() {
    var fileSelect = document.getElementById('file-upload'),
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
      //uploadFile(f);
      fileToUpload = f;
      checkForBorder(f);
      //displayFileName(f.name);
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
    var uploadUrl = '/upload'; // Adjust this URL based on your Laravel setup
  
    // Start the upload
    xhr.open('POST', uploadUrl, true);
  
    // Handle the response from the server
    xhr.onload = function () {
      if (xhr.status === 200) {
        var response = JSON.parse(xhr.responseText);
        if (response.success == true) {
          displayFileName(fileToUpload.name);
          showAlert('File uploaded successfully!', 'success');
        } else {
          showAlert('Image does not have a blue background. Please upload an image with a blue background.', 'error');
        }
      }else {
        console.error('File upload failed. Status:', xhr.status);
      }
    };
  
    // Send the FormData object containing the file
    xhr.send(formData);
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

  function displayFileName(fileName) {
    var fileNameElement = document.getElementById('file-name');
  
    // Check if the element has the 'hidden' class
    if (fileNameElement.classList.contains('hidden')) {
      // If the element is hidden, show it first and then update the content
      fileNameElement.classList.remove('hidden');
      fileNameElement.textContent = `File Name: ${fileName}`;
      return true; // Indicate success
    } else {
      // Update the content of the element with the filename
      fileNameElement.textContent = `File Name: ${fileName}`;
      return true; // Indicate success
    }
    return false; // Indicate failure (this line should not be reached)
  }       

  function parseFile(file) {
    //console.log(file.name);
    var namenyah = file.name;
    var success = displayFileName(namenyah);
    //console.log(success); // Should log true if successful
    //var fileNameElement = document.getElementById('file-name');
    //fileNameElement.classList.remove('hidden');
    

    var imageName = file.name;
    //fileNameElement.textContent = `File Name: ${imageName}`;
    var isGood = (/\.(?=gif|jpg|png|jpeg)/gi).test(imageName);

    if (isGood) {
      document.getElementById('start').classList.add("hidden");
      document.getElementById('response').classList.remove("hidden");
      document.getElementById('notimage').classList.add("hidden");
      // Thumbnail Preview
      document.getElementById('file-image').classList.remove("hidden");
      document.getElementById('file-image').src = URL.createObjectURL(file);
    } else {
      document.getElementById('file-image').classList.add("hidden");
      document.getElementById('notimage').classList.remove("hidden");
      document.getElementById('start').classList.remove("hidden");
      document.getElementById('response').classList.add("hidden");
    }
  }

  async function checkForBorder(imageElement) {
    try {
      // Load the pre-trained DeepLab model
      const model = await tf.loadGraphModel('https://tfhub.dev/tensorflow/tfjs-model/deeplab/1/default/1');

      // Preprocess the image (convert to tensor, resize, normalize)
      const tensor = tf.browser.fromPixels(imageElement);
      const resized = tf.image.resizeBilinear(tensor, [513, 513]).toFloat();
      const normalized = tf.div(tf.sub(resized, [127.5, 127.5, 127.5]), [127.5, 127.5, 127.5]);
      const input = normalized.reshape([1, 513, 513, 3]);

      // Run inference
      const predictions = await model.executeAsync(input);

      // Process predictions and check for borders
      // You can process the predictions to determine if there are borders in the image

      // Clean up
      tensor.dispose();
      resized.dispose();
      normalized.dispose();
      input.dispose();
      predictions.dispose();
    } catch (error) {
      console.error('Error:', error);
    }
  }

  // Attach the uploadFile function to the submit button click event
  document.getElementById('submit-button').addEventListener('click', function (e) {
    e.preventDefault();
    uploadFile();
  });

  // Check for the various File API support.
  if (window.File && window.FileList && window.FileReader) {
    Init();
  } else {
    document.getElementById('file-drag').style.display = 'none';
  }
}

ekUpload();