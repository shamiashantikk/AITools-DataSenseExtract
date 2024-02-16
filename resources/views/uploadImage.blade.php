<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
</head>
    <body>
        <form id="upload-form" action="{{ route('upload.image') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="frame">
            <div class="center">
                <div class="title">
                    <h1>Drop file to upload</h1>
                </div>

                <div class="dropzone">
                    <img src="http://100dayscss.com/codepen/upload.svg" class="upload-icon" />
                    <input type="file" class="upload-input" name="image" />
                </div>

                <button type="button" class="btn" name="uploadbutton" onclick="uploadImage()">Upload</button>
            </div>
        </div>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.upload-input').change(function () {
                // Automatically submit the form when a file is selected
                $('#upload-form').submit();
            });
        }); 

        // Add this script to handle the response after image upload
        $('#upload-form').submit(function (e) {
            e.preventDefault();
            var formData = new FormData(this);

        // Include CSRF token in the headers
    var headers = {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    };

        $.ajax({
        type: 'POST',
        url: '/upload-image',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Manipulasi UI atau tunjukkan mesej respons di sini
            console.log(response.message);
        }, 
        error: function (error) {
            console.error('Error during image upload:', error);
        }
        });

    });
    </script>

    </body>
</html>