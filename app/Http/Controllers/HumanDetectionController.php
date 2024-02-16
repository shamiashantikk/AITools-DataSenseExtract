<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HumanDetectionController extends Controller
{
    public function showUploadForm()
    {
        return view('upload_image');
    }
    public function detectHuman(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('image');
        $imageName = uniqid('image_') . '.' . $image->getClientOriginalExtension();

        // Save the uploaded image temporarily
        $path = $image->storeAs('temp', $image->getClientOriginalName());

        // Construct the command to execute the Python script
        $command = "python " . base_path("face_detection_wrapper.py") . " " . storage_path("app/$path");

        // Log the command
        \Log::info("Executing command: $command");

        // Execute the command and capture the output
        $output = shell_exec($command);

        // Log the output
        \Log::info("Python script output: $output");

        // Check if output is null or empty
        if ($output === null || empty(trim($output))) {
            // Log error
            \Log::error("Error executing Python script. Output: $output");

            // Return an error response or redirect back with a message
            return redirect()->back()->with('error', 'Error executing Python script. Please try again.');
        }

        // Convert the output to a boolean value
        $result = filter_var(trim($output), FILTER_VALIDATE_BOOLEAN);

        // Return the result to the view
        return view('result', compact('result'));
    }
}
