#!/bin/bash
/usr/bin/pngquant --skip-if-larger "$1/build/img/grills"/*.png
/usr/bin/pngquant --skip-if-larger "$1/build/img"/*.png
for file in "$1/build/img/grills"/*; do [ -f "$file" ] && newname=$(echo "$file" | sed 's/-fs8//g') && [ "$file" != "$newname" ] && mv "$file" "$newname" && echo "Renamed: $file -> $newname"; done
for file in "$1/build/img"/*; do [ -f "$file" ] && newname=$(echo "$file" | sed 's/-fs8//g') && [ "$file" != "$newname" ] && mv "$file" "$newname" && echo "Renamed: $file -> $newname"; done

exit 0;
