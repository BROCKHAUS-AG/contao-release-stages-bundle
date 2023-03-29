#! /bin/bash
# This file should backup the file system
# how to use flags:
#   -f'path/from' -> here comes the path which should be compressed
#   -t'path/to' -> here comes the path where the backup should be saved

source "$(dirname $0)/../create_state.sh"

while getopts f:t:n: flag
do
  case "${flag}" in
    f) from_path=${OPTARG};;
    t) to_path=${OPTARG};;
    n) name=${OPTARG};;
  esac
done

STATE_FILE="$(dirname $0)/$name"

create_pending_file "$STATE_FILE"

final_path="$to_path"
rm -r -f "$final_path"
mkdir -p "$final_path"
chmod -R 0777 "$final_path"

FILE_SYSTEM_PATH="$final_path/$name.tar.gz"
{
  tar czf "$FILE_SYSTEM_PATH" --directory="$from_path" .
} || {
  create_finish_failure_file "$STATE_FILE"
  exit
}

create_finish_success_file "$STATE_FILE"
