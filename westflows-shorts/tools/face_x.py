"""Find the horizontal crop for a 9:16 slice that keeps the speaker's face centred.
Usage: python3 face_x.py VIDEO FROM TO
Samples 2 frames per second, prints face centre per sample, the suggested crop X (source pixels)
and MOVES if the face travels too far for one fixed crop.
Uses OpenCV's YuNet face detector (tools/models/yunet.onnx). The old Haar cascade mistook the
loft insulation for faces in wide shots, so it is only a fallback if the model file is missing."""
import json, os, subprocess, sys
import cv2, numpy as np

src, start, end = sys.argv[1], float(sys.argv[2]), float(sys.argv[3])
info = json.loads(subprocess.run(
    ["ffprobe", "-v", "error", "-select_streams", "v:0", "-show_entries", "stream=width,height",
     "-of", "json", src], capture_output=True, check=True).stdout)["streams"][0]
W, H = info["width"], info["height"]
sw, sh = 960, int(round(960 * H / W / 2) * 2)
raw = subprocess.run(
    ["ffmpeg", "-v", "error", "-ss", str(start), "-t", str(end - start), "-i", src,
     "-vf", f"fps=2,scale={sw}:{sh}", "-f", "rawvideo", "-pix_fmt", "bgr24", "-"],
    capture_output=True, check=True).stdout
frames = np.frombuffer(raw, np.uint8).reshape(-1, sh, sw, 3)

model = os.path.join(os.path.dirname(os.path.abspath(__file__)), "models", "yunet.onnx")
if os.path.exists(model):
    yunet = cv2.FaceDetectorYN.create(model, "", (sw, sh), 0.7)

    def detect(im):
        found = yunet.detect(im)[1]
        return [] if found is None else [tuple(r[:4]) for r in found]
else:
    casc = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
    detect = lambda im: list(casc.detectMultiScale(cv2.cvtColor(im, cv2.COLOR_BGR2GRAY), 1.1, 5, minSize=(40, 40)))

centres = []
for i, im in enumerate(frames):
    faces = detect(im)
    if len(faces):
        x, y, w, h = max(faces, key=lambda r: r[2] * r[3])
        cx = (x + w / 2) * W / sw
        centres.append(cx)
        print(f"{start + i / 2:7.1f}s  face x={cx:.0f}")
    else:
        print(f"{start + i / 2:7.1f}s  no face")
crop_w = int(H * 9 / 32) * 2
if not centres:
    print(f"No face found. Centre crop X = {(W - crop_w) // 2}"); sys.exit()
med = float(np.median(centres))
x = int(min(max(med - crop_w / 2, 0), W - crop_w)) // 2 * 2
spread = float(np.percentile(centres, 90) - np.percentile(centres, 10))
print(f"crop width {crop_w}, suggested CROPX {x}, face spread {spread:.0f}px")
if spread > crop_w * 0.3:
    print("MOVES: split this moment at a sentence boundary and crop each part separately")
