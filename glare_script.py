import numpy as np
import argparse
import cv2

# construct the argument parse and parse the arguments
ap = argparse.ArgumentParser()
ap.add_argument("-i", "--image", help="path to the image file")
args = vars(ap.parse_args())

# load the input image
image = cv2.imread(args["image"])

# load the pre-trained Haar cascades for face detection
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

# convert the image to grayscale
gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

# detect faces in the grayscale image
faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(30, 30))

# loop over the detected faces
for (x, y, w, h) in faces:
    # extract the ROI (Region of Interest) of the face
    roi_gray = gray[y:y+h, x:x+w]
    roi_color = image[y:y+h, x:x+w]

    # detect potential glare regions around the eyes
    potential_glare = cv2.Canny(roi_gray, 80, 150)
    
    # find contours in the potential glare region
    contours, _ = cv2.findContours(potential_glare, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    # loop over the contours
    for contour in contours:
        # compute the area of the contour
        area = cv2.contourArea(contour)
        
        # if the area is small, it's likely not glare
        if area < 100:
            continue

        # compute the bounding box of the contour and draw it on the image
        (x_g, y_g, w_g, h_g) = cv2.boundingRect(contour)
        cv2.rectangle(roi_color, (x_g, y_g), (x_g + w_g, y_g + h_g), (0, 255, 0), 2)

# display the results
cv2.imshow("Glare Detection", image)
cv2.waitKey(0)
