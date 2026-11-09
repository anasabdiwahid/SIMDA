#!/bin/bash
# usage: shot.sh <input file> <output png> <width> <height> [scale]
in=$(cygpath -am "$1"); out=$(cygpath -aw "$2"); s=${5:-1}
"/c/Program Files/Google/Chrome/Application/chrome.exe" --headless=new --disable-gpu --hide-scrollbars --force-device-scale-factor=$s --default-background-color=00000000 --user-data-dir="C:/Users/hp/AppData/Local/Temp/claude/b7ce5fc8451cf5f1/789013d3-6d42-43ba-b3f2-c5c67a31aa21/scratchpad/chrome-$$-$RANDOM" --window-size=$3,$4 --virtual-time-budget=4000 --screenshot="$out" "file:///$in" >/dev/null 2>&1
ls -la "$2" | awk '{print $5, $9}'
