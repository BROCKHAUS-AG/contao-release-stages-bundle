#! /bin/bash
#   -f'path/from' -> here comes the path from the archive
#   -e'path/to' -> here comes the path where the extracted files should be saved
#   -n'name' -> here comes the name of the process

. ~/scripts/create_state.sh

while getopts f:e:n: flag
do
  case "${flag}" in
    f) file=${OPTARG};;
    e) extracted_path=${OPTARG};;
    n) name=${OPTARG};;
  esac
done

STATE_FILE="$(dirname $0)/un_archive_$name"

create_pending_file "$STATE_FILE"

rm -R "$extracted_path"
mkdir -p "$extracted_path"

{
  tar -xvf $file -C $extracted_path
} || {
  create_finish_failure_file "$STATE_FILE"
  exit
}

create_finish_success_file "$STATE_FILE"

