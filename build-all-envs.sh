#!/bin/bash
CURRENT_FOLDER=$('pwd')
CONFIGS="config/*/"
for FOLDER in $CONFIGS
do
  if [ -d "$FOLDER" ]
  then
      echo "Starting with... \"$FOLDER\""
      ENV_NAME=$(cut -d '/' -f 2 <<< $FOLDER)
      ./build-packages-without-log.sh -e $ENV_NAME
  else
    echo "Warning: Some problem with \"$FOLDER\""
  fi
done
