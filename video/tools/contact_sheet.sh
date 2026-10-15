#!/usr/bin/env bash
# One frame per second, timestamped, tiled: lets Claude "watch" a clip as a single image.
# Usage: contact_sheet.sh INPUT OUTPUT.jpg [SECONDS_PER_FRAME]
set -euo pipefail
step=${3:-1}
font=$(fc-list : file | grep -i 'DejaVuSans-Bold.ttf' | head -1 | cut -d: -f1)
dur=$(ffprobe -v error -show_entries format=duration -of csv=p=0 "$1")
rows=$(python3 -c "import math;print(max(1,math.ceil($dur/$step/10)))")
ffmpeg -v error -y -i "$1" -vf "fps=1/$step,scale=180:-2,drawtext=fontfile=$font:text='%{eif\:t\:d}s':x=4:y=4:fontsize=18:fontcolor=white:box=1:boxcolor=black@0.6,tile=10x$rows:padding=3:color=white" -frames:v 1 "$2"
