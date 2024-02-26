import cv2
import sys
import dlib
import numpy as np

# Load the face detector and shape predictor
detector = dlib.get_frontal_face_detector()
predictor = dlib.shape_predictor("shape_predictor_68_face_landmarks.dat")

def detect_faces_and_eyes(image):
    # Convert the image to grayscale
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    # Detect the faces
    rects = detector(gray)  

    face_detected = False
    left_eye_detected = False
    right_eye_detected = False
    left_eye_glare = False
    right_eye_glare = False

    for rect in rects:
        face_detected = True

        # Extract the coordinates of the bounding box
        x1 = rect.left()
        y1 = rect.top()
        x2 = rect.right()
        y2 = rect.bottom()

        # Draw a rectangle around the face
        cv2.rectangle(image, (x1, y1), (x2, y2), (0, 255, 0), 2)

        # Apply the shape predictor to the face ROI
        shape = predictor(gray, rect)
        
        # Extract the coordinates of the eyes
        left_eye = []
        right_eye = []
        for n in range(36, 42):
            x = shape.part(n).x
            y = shape.part(n).y
            left_eye.append((x, y))
            
        for n in range(42, 48):
            x = shape.part(n).x
            y = shape.part(n).y
            right_eye.append((x, y))

        # Convert eye points to numpy array
        left_eye = np.array(left_eye)
        right_eye = np.array(right_eye)

        # Get the bounding box of the eyes
        left_eye_rect = cv2.boundingRect(left_eye)
        right_eye_rect = cv2.boundingRect(right_eye)

        # Expand the bounding box by a factor (e.g., 2.5)
        expand_factor = 2.5
        left_eye_rect_expanded = (left_eye_rect[0] - int(left_eye_rect[2] * (expand_factor - 1) / 2),
                                  left_eye_rect[1] - int(left_eye_rect[3] * (expand_factor - 1) / 2),
                                  int(left_eye_rect[2] * expand_factor),
                                  int(left_eye_rect[3] * expand_factor))
        right_eye_rect_expanded = (right_eye_rect[0] - int(right_eye_rect[2] * (expand_factor - 1) / 2),
                                   right_eye_rect[1] - int(right_eye_rect[3] * (expand_factor - 1) / 2),
                                   int(right_eye_rect[2] * expand_factor),
                                   int(right_eye_rect[3] * expand_factor))

        # Draw rectangles around the eyes
        cv2.rectangle(image, (left_eye_rect_expanded[0], left_eye_rect_expanded[1]),
                      (left_eye_rect_expanded[0] + left_eye_rect_expanded[2], left_eye_rect_expanded[1] + left_eye_rect_expanded[3]),
                      (255, 0, 0), 2)
        cv2.rectangle(image, (right_eye_rect_expanded[0], right_eye_rect_expanded[1]),
                      (right_eye_rect_expanded[0] + right_eye_rect_expanded[2], right_eye_rect_expanded[1] + right_eye_rect_expanded[3]),
                      (255, 0, 0), 2)

        # Check if left eye is detected
        if left_eye_rect[2] > 0 and left_eye_rect[3] > 0:
            left_eye_detected = True
            # Extract left eye region
            left_eye_region = gray[left_eye_rect_expanded[1]:left_eye_rect_expanded[1]+left_eye_rect_expanded[3],
                                   left_eye_rect_expanded[0]:left_eye_rect_expanded[0]+left_eye_rect_expanded[2]]
            # Check for glare in left eye region
            # left_eye_glare = detect_glare(left_eye_region)

        # Check if right eye is detected
        if right_eye_rect[2] > 0 and right_eye_rect[3] > 0:
            right_eye_detected = True
            # Extract right eye region
            right_eye_region = gray[right_eye_rect_expanded[1]:right_eye_rect_expanded[1]+right_eye_rect_expanded[3],
                                     right_eye_rect_expanded[0]:right_eye_rect_expanded[0]+right_eye_rect_expanded[2]]
            # Check for glare in right eye region
            # right_eye_region_image = cv2.cvtColor(right_eye_region, cv2.COLOR_BGR2GRAY)
            # Check for glare in right eye region
            # print("Calling detect_glare for right eye")
            # right_eye_glare = detect_glare(right_eye_region_image)

    return image, face_detected, left_eye_detected, right_eye_detected, left_eye_glare, right_eye_glare

def detect_glare(eye_region):
    # Apply Gaussian blur to reduce noise and improve glare detection
    blurred_eye = cv2.GaussianBlur(eye_region, (5, 5), 0)
    
    # Apply adaptive thresholding to segment the eye region
    _, threshold_eye = cv2.threshold(blurred_eye, 50, 255, cv2.THRESH_BINARY)
    
    # Find contours in the thresholded image
    contours, _ = cv2.findContours(threshold_eye, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    
    # Check if any contours are found
    if contours:
        # Calculate the area of the largest contour
        max_area = max(contours, key=cv2.contourArea)
        contour_area = cv2.contourArea(max_area)
        
        # Define a threshold for glare detection based on contour area
        glare_threshold = 2000  # Adjust this threshold based on your requirements
        
        # Check if the contour area exceeds the glare threshold
        if contour_area > glare_threshold:
            return True  # Glare detected
    return False  # No glare detected

# Get the path to the image file from the command-line arguments
image_path = sys.argv[1]

# Read the image from the specified path
image = cv2.imread(image_path)

# Detect faces and eyes in the image
image_with_boxes, face_detected, left_eye_detected, right_eye_detected, left_eye_glare, right_eye_glare = detect_faces_and_eyes(image)

# Print the results
print("Face detected:", face_detected)
print("Left eye detected:", left_eye_detected)
print("Right eye detected:", right_eye_detected)
print("Left eye glare detected:", left_eye_glare)
print("Right eye glare detected:", right_eye_glare)

# Display the image with bounding boxes
cv2.imshow("Image with Bounding Boxes", image_with_boxes)
cv2.waitKey(0)
cv2.destroyAllWindows()
