import cv2
import argparse

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
    blur_text = "Not Blurry"

    # Check blur condition based on variance of Laplacian image
    if laplacian_variance < threshold:
        blur_text = "Blurry"

    # Print result
    print("Image:", image_path)
    print("Blur status:", blur_text)

def main():
    # Parse command-line arguments
    parser = argparse.ArgumentParser()
    parser.add_argument("-i", required=True, help="input image file path")
    parser.add_argument("-t", type=float, default=250.0, help="blur threshold")
    args = parser.parse_args()

    # Process the specified image
    detect_blur(args.i, args.t)

if __name__ == "__main__":
    main()
