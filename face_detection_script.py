import cv2
import sys

# Function to check the human face in the picture
def is_human_opencv(image_path):
    # download image from path
    img = cv2.imread(image_path)
    
    # Convert the image to grayscale
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    # Load the Haarcascades model for face checking
    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml') 

    # Face check
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.3, minNeighbors=5)

    # If there is a face, return True (human image)
    return len(faces) > 0

# Examples of use
if __name__ == "__main__":
    # Get the image path from the command line arguments
    image_path = sys.argv[1]

    result = is_human_opencv(image_path)

    # Print the result to standard output
    print(result)
