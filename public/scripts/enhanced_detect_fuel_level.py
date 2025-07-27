import cv2
import numpy as np
import sys
import math
import os

def debug(*args, **kwargs):
    if False:
        print(*args, **kwargs)

def detect_fuel_level(image_path):
    if not os.path.exists(image_path):
        print("ERROR: Image not found", file=sys.stderr)
        return
    
    try:
        img = cv2.imread(image_path)
        if img is None:
            print("ERROR: Failed to load image", file=sys.stderr)
            return

        h, w = img.shape[:2]
        
        crop_height = int(h * 0.30)
        crop_width = int(w * 0.50)
        fuel_crop = img[h - crop_height:h, w - crop_width:w]
        
        # Preprocessing
        gray = cv2.cvtColor(fuel_crop, cv2.COLOR_BGR2GRAY)
        blur = cv2.GaussianBlur(gray, (7, 7), 0)
        edges = cv2.Canny(blur, 50, 150)
        
        debug("Detecting circles...")
        circles = cv2.HoughCircles(
            blur, 
            cv2.HOUGH_GRADIENT, 
            dp=1, 
            minDist=100,
            param1=50,
            param2=30,
            minRadius=20,
            maxRadius=min(crop_height, crop_width) // 3
        )
        
        gauge_circle = None
        if circles is not None:
            circles = np.uint16(np.around(circles))
            # Select largest circle
            circles = sorted(circles[0], key=lambda x: x[2], reverse=True)
            gauge_circle = circles[0]
            debug(f"Found circle: {gauge_circle}")
            
        
        debug("Detecting lines...")
        lines = cv2.HoughLinesP(
            edges, 
            rho=1, 
            theta=np.pi/180, 
            threshold=30, 
            minLineLength=20, 
            maxLineGap=10
        )
        
        if gauge_circle:
            cx, cy, r = gauge_circle[0], gauge_circle[1], gauge_circle[2]
            needle = None
            max_length = 0
            
            if lines is not None:
                for line in lines:
                    x1, y1, x2, y2 = line[0]
                    length = math.sqrt((x2-x1)**2 + (y2-y1)**2)
                    
                    dist1 = math.sqrt((x1-cx)**2 + (y1-cy)**2)
                    dist2 = math.sqrt((x2-cx)**2 + (y2-cy)**2)
                    
                    if (dist1 < r*0.5 or dist2 < r*0.5) and length > max_length:
                        max_length = length
                        needle = ((x1, y1), (x2, y2))
            
            if needle:
                (x1, y1), (x2, y2) = needle
                # Calculate angle from vertical (12 o'clock position)
                angle = math.degrees(math.atan2(cy - (y1+y2)//2, (x1+x2)//2 - cx))
                angle = (angle + 360) % 360  
                
                debug(f"Needle angle: {angle:.1f}°")
                
                # Map angle to fuel level (assuming E at 225°, F at 315°)
                angle_from_start = (angle - 225) % 360
                if angle_from_start < 0:
                    angle_from_start += 360
                
                if angle_from_start < 45:
                    level = "Full"
                elif angle_from_start < 135:
                    level = "3/4"
                elif angle_from_start < 225:
                    level = "1/2"
                else:
                    level = "1/4"
                    
                print(level)
                return
        
        debug("Using fallback method...")
        angles = []
        if lines is not None:
            for line in lines:
                x1, y1, x2, y2 = line[0]
                angle = math.degrees(math.atan2(y2 - y1, x2 - x1))
                angles.append(angle)
        
        if angles:
            avg_angle = np.mean(angles)
            debug(f"Average angle: {avg_angle:.1f}°")
            
            if avg_angle < -45:
                print("Empty")
            elif avg_angle < 45:
                print("1/2")
            else:
                print("Full")
            return
        
        print("Unknown")
        
    except Exception as e:
        print(f"ERROR: {str(e)}", file=sys.stderr)

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python detect_fuel_level.py <image_path>")
        sys.exit(1)
    
    detect_fuel_level(sys.argv[1])