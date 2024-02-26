import json
import cv2
import sys
import os
import dlib
import numpy as np

# Load the face detector and shape predictor
detector = dlib.get_frontal_face_detector()
# file_path = os.path.abspath("shape_predictor_68_face_landmarks.dat")
predictor = dlib.shape_predictor("shape_predictor_68_face_landmarks.dat")

def detect_faces_and_eyes(image):
    # download image from path
    img = cv2.imread(image)
    
    # Convert the image to grayscale
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    # Detect the faces
    rects = detector(gray)  

    face_detected = False
    multiple_faces_detected = False
    left_eye_detected = False
    right_eye_detected = False
    landmarks = {
        "face": None,
        "left_eye": None,
        "right_eye": None
    }

    # multiple faces detected
    num_faces = len(rects)
    if num_faces > 1:
        multiple_faces_detected = True

    for rect in rects:
        face_detected = True

        # Extract the coordinates of the bounding box
        x1 = rect.left()
        y1 = rect.top()
        x2 = rect.right()
        y2 = rect.bottom()

        landmarks["face"] = {"x1": x1, "y1": y1, "x2": x2, "y2": y2}

        # Draw a rectangle around the face
        cv2.rectangle(img, (x1, y1), (x2, y2), (0, 255, 0), 2)

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
        left_eye_rect = (left_eye_rect[0] - int(left_eye_rect[2] * (expand_factor - 1) / 2),
                         left_eye_rect[1] - int(left_eye_rect[3] * (expand_factor - 1) / 2),
                         int(left_eye_rect[2] * expand_factor),
                         int(left_eye_rect[3] * expand_factor))
        right_eye_rect = (right_eye_rect[0] - int(right_eye_rect[2] * (expand_factor - 1) / 2),
                          right_eye_rect[1] - int(right_eye_rect[3] * (expand_factor - 1) / 2),
                          int(right_eye_rect[2] * expand_factor),
                          int(right_eye_rect[3] * expand_factor))

        # Draw rectangles around the eyes
        cv2.rectangle(img, (left_eye_rect[0], left_eye_rect[1]),
                      (left_eye_rect[0] + left_eye_rect[2], left_eye_rect[1] + left_eye_rect[3]),
                      (255, 0, 0), 2)
        cv2.rectangle(img, (right_eye_rect[0], right_eye_rect[1]),
                      (right_eye_rect[0] + right_eye_rect[2], right_eye_rect[1] + right_eye_rect[3]),
                      (255, 0, 0), 2)

        # Check if left eye is detected
        if left_eye_rect[2] > 0 and left_eye_rect[3] > 0:
            left_eye_detected = True
            landmarks["left_eye"] = {
                "x1": left_eye_rect[0], 
                "y1": left_eye_rect[1], 
                "x2": left_eye_rect[0] + left_eye_rect[2], 
                "y2": left_eye_rect[1] + left_eye_rect[3]
            }
        
        # Check if right eye is detected
        if right_eye_rect[2] > 0 and right_eye_rect[3] > 0:
            right_eye_detected = True
            landmarks["right_eye"] = {
                "x1": right_eye_rect[0], 
                "y1": right_eye_rect[1], 
                "x2": right_eye_rect[0] + right_eye_rect[2], 
                "y2": right_eye_rect[1] + right_eye_rect[3]
            }

    return face_detected, multiple_faces_detected, left_eye_detected, right_eye_detected, landmarks

# Examples of use
if __name__ == "__main__":
    # Get the image path from the command line arguments
    image = ' '.join(sys.argv[1:])

    "Ensure the image path exists"
    if not os.path.exists(image):
        print("Error: The specified image path does not exist.")
        sys.exit(1)

    face_detected, multiple_faces_detected, left_eye_detected, right_eye_detected, landmarks = detect_faces_and_eyes(image)

    # Construct a dictionary containing all the detection results
    detection_results = {
        "face_detected": face_detected,
        "multiple_faces_detected": multiple_faces_detected,
        "left_eye_detected": left_eye_detected,
        "right_eye_detected": right_eye_detected,
        "landmarks": landmarks
    }

    # Convert the dictionary to a JSON string and print it
    print(json.dumps(detection_results))
