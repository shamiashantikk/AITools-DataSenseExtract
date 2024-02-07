<?php

namespace App\Http\Controllers;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Http\Request;

class glareController extends Controller
{
    public function glare()
    {
        return view('glare');
    }
    public function eyedetection()
    {
       return view('eye-detection');
    }

    public function checkGlare(Request $request)
    {
        try {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $uploadedFile = $request->file('photo');
            if (!$uploadedFile || !$uploadedFile->isValid()) {
                // Log an error or return an appropriate response
                return response()->json(['error' => 'Invalid or missing image file'], 400);
            }

            $image = Image::make($uploadedFile);

            if (!$image) {
                // Log an error or return an appropriate response
                return response()->json(['error' => 'Failed to process the image'], 500);
            }

            // Save the original image for reference
            $image->save(public_path('/sample/original_image.jpg'));
            $isGlareDetected = $this->detectGlare($image);
            if ($isGlareDetected) {
                $responseMessage = "Glare detected!";
            } else {
                $responseMessage = "No glare detected.";
            }

            return response()->json([
                'glare_status' => [
                    'headers' => [],
                    'original' => ['glare_status' => $isGlareDetected],
                    'exception' => null,
                    'message' => $responseMessage,
                ],
            ]);
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in detectGlare: ' . $e->getMessage());

            return response()->json([
                'glare_status' => [
                    'headers' => [],
                    'original' => ['error' => 'Internal Server Error'],
                    'exception' => null,
                ],
            ], 500);
        }
    }

    // private function detectGlare($image)
    // {
    //     try {
    //         $glareFound = false;

    //         // Loop through pixels
    //         for ($x = 0; $x < $image->width(); $x++) {
    //             for ($y = 0; $y < $image->height(); $y++) {

    //                 // Get pixel color
    //                 $color = $image->pickColor($x, $y);

    //                 // Check if the color is an array
    //                 if (is_array($color) && count($color) >= 3) {
    //                     $r = $color[0];
    //                     $g = $color[1];
    //                     $b = $color[2];
                    
    //                     // Continue with your existing logic...
    //                     // Calculate brightness
    //                     $brightness = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                    
    //                     // Set brightness threshold
    //                     $brightnessThreshold = 300; // Adjust this threshold as needed
                    
    //                     // Check for glare pixels
    //                     if ($brightness > $brightnessThreshold) {
    //                         $glareFound = true;
    //                         // Highlight glare pixels in red
    //                         $image->pixel([255, 0, 0], $x, $y);
    //                     }
    //                 } else {
    //                     // Log an error or use dump for debugging
    //                     \Log::error('Invalid color information: ' . json_encode($color));
                        
    //                     // Return an appropriate response
    //                     return response()->json(['error' => 'Invalid color information'], 500);
    //                 }
    //             }
    //         }

    //         // Save or display the modified image for visualization
    //         $outputImagePath = public_path('sample/outputglare3.png'); // Update path as needed
    //         $image->save($outputImagePath);

    //         if ($glareFound) {
    //             $responseMessage = "Glare detected!";
    //         } else {
    //             $responseMessage = "No glare detected.";
    //         }

    //         // Pass the path of the modified image and the response message to the view
    //         // return [
    //         //     'headers' => [],
    //         //     'original' => [
    //         //         'glare_status' => $glareFound,
    //         //     ],
    //         //     'exception' => null,
    //         //     'message' => $responseMessage,
    //         //     'outputImagePath' => $outputImagePath,
    //         // ];
            
    //         return response()->json([
    //             'glare_status' => [
    //                 'headers' => [],
    //                 'original' => ['glare_status' => $glareFound],
    //                 'exception' => null,
    //                 'message' => $responseMessage,
    //             ],
    //         ]);

    //     } catch (\Exception $e) {
    //         // Log the exception
    //         \Log::error('Error in detectGlare: ' . $e->getMessage());

    //         return response()->json([
    //             'glare_status' => [
    //                 'headers' => [],
    //                 'original' => ['error' => 'Internal Server Error'],
    //                 'exception' => null,
    //             ],
    //         ], 500);
    //     }
    // }  
    function detectGlare($imagePath)
{
    try {
        // Load the image
        $image = Image::make($imagePath);

        // Convert the image to grayscale
        $grayImage = $image->greyscale();

        // Apply a threshold
        $threshold = 250;
        $binaryImage = $grayImage->threshold($threshold);

        // Count the number of non-zero pixels
        $nonZeroCount = $binaryImage->count(0);

        // Get the total number of pixels in the image
        $totalPixels = $binaryImage->width() * $binaryImage->height();

        // Calculate the percentage of non-zero pixels
        $percentageNonZero = ($nonZeroCount / $totalPixels) * 100;

        // Define the threshold percentage
        $glareThresholdPercentage = 1; // You can adjust this threshold as needed

        // Check if the glare percentage is higher than the threshold
        $isGlareDetected = $percentageNonZero > $glareThresholdPercentage;

        return $isGlareDetected;
    } catch (\Exception $e) {
        // Handle exceptions as needed
        return false;
    }
} 
}
