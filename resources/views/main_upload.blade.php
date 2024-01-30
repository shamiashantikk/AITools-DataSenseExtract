<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('resources/css/app.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <title>Image Upload</title>
</head>
<body>
  <h2>File Upload & Image Preview</h2>
  <p class="lead">No Plugins <b>Just Javascript</b></p>
  <div class="container">
    <div class="row">
        <div class="col-md-6 mx-auto">
          <form action="{{ route('upload') }}" id="file-upload-form" class="uploader" method="post" enctype="multipart/form-data">
              @csrf
            <input id="file-upload" type="file" name="fileUpload" accept="image/*" />

            <label for="file-upload" id="file-drag">
              <img id="file-image" src="#" alt="Preview" class="hidden">
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
    <div class="row mt-4">
      <div class="col-md-5 mx-auto">
        <div id="message-box" class="hidden" role="alert"></div>
      </div>
    </div>
  </div>
  <script src="{{ mix('resources/js/app.js') }}" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
