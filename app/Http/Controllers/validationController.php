<?php

namespace App\Http\Controllers;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;

class validationController extends Controller
{
    public function showForm()
    {
        $isBlueBackground = false; // Set to the actual value
        $isHuman = false; // Set to the actual value
        return view('main_upload', compact('isBlueBackground', 'isHuman'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'fileUpload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('fileUpload');
        $imagePath = $image->path();

        // Save the uploaded image temporarily
        $path = $image->storeAs('temp', $image->getClientOriginalName());

        // Check background color before proceeding with upload
        $isBlueBackground = $this->checkBackgroundColor($imagePath);
        $isHuman = $this->detectHuman($path);

        // Detect human and retrieve detection results
        $detectionResults = $this->detectGlare($path); 
        $landmarks = $detectionResults['landmarks'];
        $leftEyeDetected = $detectionResults['left_eye_detected'];
        //var_dump($leftEyeDetected);
        $rightEyeDetected = $detectionResults['right_eye_detected'];
        $faceDetected = $detectionResults['face_detected'];
        $multipleFacesDetected = $detectionResults['multiple_faces_detected'];

        $isBlurry = $this->detectBlur($path);
        
        // Return the results in the response JSON 
        return response()->json([
            'success' => true,
            'isBlueBackground' => $isBlueBackground,
            'isHuman' => $isHuman,
            'leftEyeDetected' => $leftEyeDetected,
            'rightEyeDetected' => $rightEyeDetected,
            'faceDetected' => $faceDetected,
            'landmarks' => $landmarks,
            'multipleFacesDetected' => $multipleFacesDetected,
            'isBlurry' => $isBlurry
        ]);
    }

    public function checkBackgroundColor($imagePath, $threshold = 200)
    {
        // Open an image file
        $img = Image::make($imagePath);

        // Resize the image to a small size for faster processing (optional)
        $img->resize(10, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        // Limit the image to a specific number of colors (e.g., 1)
        $limitedColorsImage = $img->limitColors(1);

        // Get the most dominant color in the limited colors image
        $dominantColor = $limitedColorsImage->pickColor(0, 0, 'array');

        // Adjust threshold dynamically based on brightness
        $adjustedThreshold = $this->calculateAdjustedThreshold($dominantColor, $threshold);

        // Check if the color is not blue or near blue
        $isBlue = $this->isBlueColor($dominantColor, $adjustedThreshold);

        return $isBlue;
    }

    public function calculateAdjustedThreshold($color, $baseThreshold = 200)
    {
        // Extract individual RGB components
        $red = $color[0];
        $green = $color[1];
        $blue = $color[2];

        // Calculate brightness of the color
        $brightness = ($red + $green + $blue) / 3;

        // Adjust threshold based on brightness
        // You can adjust these coefficients as needed
        $adjustedThreshold = $baseThreshold + ($brightness - 85) * 0.5;

        // Ensure threshold is within reasonable range
        $adjustedThreshold = min(max($adjustedThreshold, 0), 255);

        return $adjustedThreshold;
    }

    public function isBlueColor($color, $threshold = 200)
    {
        // Extract individual RGB components
        $red = $color[0];
        $green = $color[1];
        $blue = $color[2];

        // Calculate the Euclidean distance from the color to the blue reference (0, 0, 255)
        $distance = sqrt(pow($red, 2) + pow($green, 2) + pow(($blue - 255), 2));

        // Check if the distance is below the threshold
        return $distance < $threshold;
    }

    public function detectHuman($imagePath)
    {
        $command = "python " . base_path("face_detection_script.py") . " " . storage_path("app/$imagePath");

        // Execute the command
        exec($command, $output, $returnCode);

        // Check if the command executed successfully
        if ($returnCode === 0) {
            // Extract the result from the output
            $result = trim(implode("\n", $output));
            // Convert the result to a boolean value
            $isHuman = filter_var($result, FILTER_VALIDATE_BOOLEAN);

            return $isHuman;
        } else {
            return false;
        }
    }

    public function detectBlur($imagePath)
    {
        $command = "python " . base_path("blurry_detection.py") . " -i " . storage_path("app/$imagePath");

        // Execute the command
        exec($command, $output, $returnCode);

        // Check if the command executed successfully
        if ($returnCode === 0) {
            // Extract the result from the output
            $result = trim(implode("\n", $output));
            
            // Convert the result to a boolean value
            $isBlurry = strtolower($result) === 'true';

            return $isBlurry;
        } else {
            return false;
        }
    }

    public function detectGlare($imagePath)
    {
        $command = "python " . base_path("glare_script.py") . " " . storage_path("app/$imagePath");

        // Execute the command
        exec($command, $output, $returnCode);

        // Check if the command executed successfully
        if ($returnCode === 0) {
            // Parse the output JSON string
            $result = json_decode($output[0], true);

            // Extract individual results
            $faceDetected = $result['face_detected'];
            $multipleFacesDetected = $result['multiple_faces_detected'];
            $leftEyeDetected = $result['left_eye_detected'];
            $rightEyeDetected = $result['right_eye_detected'];
            $landmarks = $result['landmarks'];
            return [
                'face_detected' => $faceDetected,
                'multiple_faces_detected' => $multipleFacesDetected,
                'left_eye_detected' => $leftEyeDetected,
                'right_eye_detected' => $rightEyeDetected,
                'landmarks' => $landmarks
            ];
        } else {
            return false;
        }
    }
}
