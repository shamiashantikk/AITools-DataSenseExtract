import cv2
import argparse
import os
import sys

def detect_blur(image_path, threshold):
    # Read the image
    image = cv2.imread(image_path)

    # Convert image to grayscale
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    # Apply Laplacian filter for edge detection
    laplacian = cv2.Laplacian(gray, cv2.CV_64F)

    # Calculate variance of Laplacian
    laplacian_variance = laplacian.var()

    # Initialize result variable
    is_blurry = laplacian_variance < threshold

    return is_blurry

def main():
    # Parse command-line arguments
    parser = argparse.ArgumentParser()
    parser.add_argument("-i", nargs='+', required=True, help="input image file path")
    parser.add_argument("-t", type=float, default=250.0, help="blur threshold")
    args = parser.parse_args()

    # Construct the image path by joining the arguments
    image_path = ' '.join(args.i)

    # Ensure the image path exists
    if not os.path.exists(image_path):
        print("Error: The specified image path does not exist.")
        sys.exit(1)

    # Process the specified image
    is_blurry = detect_blur(image_path, args.t)

    # Modify the return value to suit your needs (e.g., print as JSON)
    print(is_blurry)

if __name__ == "__main__":
    main()
