#!/bin/sh


mainPath=$(echo "$1")
satisConfig=$(echo "$2")
satisOutputBuild=$(echo "$3")

echo "mainPath: $mainPath"
echo "satisConfig: $satisConfig"
echo "satisOutputBuild: $satisOutputBuild"





docker run --rm --init --tty \
          --user $(id -u):$(id -g) \
          --volume $(pwd):$mainPath \
          --volume "$(pwd)/composer:/composer" \
          composer/satis build $satisConfig $satisOutputBuild
