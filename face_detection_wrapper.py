import sys
from face_detection_script import is_human_opencv

# Get the image path from command line arguments
image_path = sys.argv[1]

# Call the function and print the result
result = is_human_opencv(image_path)
print(result)
