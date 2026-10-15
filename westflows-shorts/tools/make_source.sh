#!/usr/bin/env bash
# Cut a moment from the long video, crop to 9:16 at CROPX, scale to 1080x1920, clean the voice.
# Usage: make_source.sh VIDEO FROM TO CROPX OUT.mp4
set -euo pipefail
GRADE="${GRADE:-null}"   # e.g. GRADE="eq=gamma=1.03:saturation=1.05" after testing against references
mkdir -p "$(dirname "$5")"
dur=$(python3 -c "print(round($3 - $2, 3))")
ffmpeg -v error -y -ss "$2" -t "$dur" -i "$1" \
  -vf "crop=trunc(ih*9/32)*2:ih:$4:0,scale=1080:1920:flags=lanczos,$GRADE" \
  -af "highpass=f=80,acompressor=threshold=-20dB:ratio=3:attack=5:release=120,loudnorm=I=-14:TP=-1.5:LRA=11" \
  -r 30 -c:v libx264 -crf 18 -preset medium -g 15 -pix_fmt yuv420p \
  -c:a aac -b:a 192k -ar 48000 "$5"
echo "made $5 ($dur s)"
