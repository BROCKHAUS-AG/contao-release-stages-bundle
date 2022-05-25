#! /bin/bash
# This file should backup the file system
# how to use flags:
#   -f'path/from' -> here comes the path which should be compressed
#   -t'path/to' -> here comes the path where the backup should be saved

. ~/scripts/create_state.sh

while getopts f:t: flag
do
  case "${flag}" in
    f) from_path=${OPTARG};;
    t) to_path=${OPTARG};;
  esac
done

STATE_FILE="$(dirname $0)/file_system_backup"

create_pending_file "$STATE_FILE"

mkdir -p "$to_path"

FILE_SYSTEM_PATH="$to_path/file_system_backup.tar.gz"
{
  tar -zcvf $FILE_SYSTEM_PATH $from_path
} || {
  create_finish_failure_file "$STATE_FILE"
}

create_finish_success_file "$STATE_FILE"
