#!/usr/bin/env bash
# Build every short in library/<slug>/shorts/ whose finished MP4 is missing (resume after a restart).
# Usage: tools/build_missing.sh <slug>          Skips if a build is already running.
set -uo pipefail
cd "$(dirname "$0")/.."
slug=$1
if pgrep -f "^python3 tools/[b]uild_short.py" >/dev/null; then echo "build already running"; exit 0; fi
for spec in library/$slug/shorts/*.json; do
  name=$(python3 -c "import json,sys;s=json.load(open(sys.argv[1]));print(f\"{s['id']}-{s['name']}\")" "$spec")
  [ -f "out/$slug/$name.mp4" ] && continue
  echo "== $name $(date +%H:%M)"
  python3 tools/build_short.py "$spec" 2>&1 | grep -E "block|segments|output|DONE|Error|Traceback"
done
echo "ALL BUILT $(date +%H:%M)"
