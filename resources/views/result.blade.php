<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result</title>
</head>
<body>
    @if($result)
        <h1>The uploaded image contains a human face.</h1>
    @else
        <h1>No human face detected in the uploaded image.</h1>
    @endif
</body>
</html>
