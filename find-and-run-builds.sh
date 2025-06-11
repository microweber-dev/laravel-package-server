#!/bin/bash

find /home/modules/code/storage/repositories-satis -type f -name "command.sh" | while read cmd; do
    bash "$cmd"
    folder_name=$(basename "$(dirname "$cmd")")
    php artisan check-status --id="$folder_name"
done
