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

        // Check if the color is not blue or near blue
        $isBlue = $this->isBlueColor($dominantColor, $threshold);

        return $isBlue;
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

        // Combine all results into the response JSON
        return response()->json([
            'success' => true,
            'isBlueBackground' => $isBlueBackground,
            'isHuman' => $isHuman
        ]);
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
}
