import cv2
import numpy as np
import sys
import math

def detect_fuel_level(image_path):
    img = cv2.imread(image_path)

    h, w, _ = img.shape
    fuel_crop = img[int(h*0.70):int(h*0.95), int(w*0.75):int(w*0.95)]  

    gray = cv2.cvtColor(fuel_crop, cv2.COLOR_BGR2GRAY)
    blur = cv2.GaussianBlur(gray, (5, 5), 0)

    edges = cv2.Canny(blur, 50, 150)

    lines = cv2.HoughLinesP(edges, 1, np.pi/180, 30, minLineLength=10, maxLineGap=10)

    center_x = fuel_crop.shape[1] // 2
    center_y = fuel_crop.shape[0] // 2

    angles = []

    if lines is not None:
        for line in lines:
            x1, y1, x2, y2 = line[0]
            angle = math.degrees(math.atan2(y2 - y1, x2 - x1))
            angles.append(angle)

    if not angles:
        print("Unknown")
        return

    avg_angle = np.mean(angles)

    if avg_angle < -30:
        print("Empty")
    elif -30 <= avg_angle <= 30:
        print("Half")
    elif avg_angle > 30:
        print("Full")
    else:
        print("Unknown")

if __name__ == "__main__":
    detect_fuel_level(sys.argv[1])
