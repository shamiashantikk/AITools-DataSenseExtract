<?php

namespace App\Http\Controllers;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;

class validationController extends Controller
{
    public function showForm()
    {
        return view('main_upload');
    }

    public function checkBackgroundColor($imagePath, $threshold = 150)
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

    public function isBlueColor($color, $threshold = 150)
    {
        // Check if the color is close to blue (adjust the RGB values as needed)
        $blueThreshold = $threshold;
        $isBlue = $color[0] < $blueThreshold && $color[1] < $blueThreshold && $color[2] > (255 - $blueThreshold);

        return $isBlue;
    }
    public function uploadImage(Request $request)
    {
        $request->validate([
            'fileUpload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('fileUpload');
        $imagePath = $image->path();

        // Check background color before proceeding with upload
        $isBlueBackground = $this->checkBackgroundColor($imagePath);

        if ($isBlueBackground == true ) {
            // Continue with your existing upload logic
            $imageName = uniqid() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads'), $imageName);

           // return "File uploaded successfully. File path: " . public_path('uploads/' . $imageName);
           return response()->json(['success' => true, 'message' => 'File uploaded successfully']);

        } else {
            //return "Image does not have a blue background. Please upload an image with a blue background.";
            return response()->json(['success' => false, 'message' => 'Image does not have a blue background. Please upload an image with a blue background.']);
        }
    }
}
