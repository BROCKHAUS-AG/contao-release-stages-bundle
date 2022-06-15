#! /bin/bash
#   -f'path/from' -> here comes the path from the archive
#   -e'path/to' -> here comes the path where the extracted files should be saved

source "$(dirname $0)/../create_state.sh"

while getopts u:p:h:d:t: flag
do
  case "${flag}" in
    f) file=${OPTARG};;
    e) extracted_path=${OPTARG};;
  esac
done

STATE_FILE="$(dirname $0)/un_archive"

create_pending_file "$STATE_FILE"

mkdir -p "$extracted_path"

{
  tar -xvf $file -C $extracted_path
} || {
  create_finish_failure_file "$STATE_FILE"
}

create_finish_success_file "$STATE_FILE"

