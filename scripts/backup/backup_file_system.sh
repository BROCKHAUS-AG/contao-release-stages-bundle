#! /bin/bash
# This file should backup the file system
# how to use flags:
#   -f'path/from' -> here comes the path which should be compressed
#   -t'path/to' -> here comes the path where the backup should be saved

while getopts f:t: flag
do
  case "${flag}" in
    f) from_path=${OPTARG};;
    t) to_path=${OPTARG};;
  esac
done

mkdir -p "$to_path"

to_path="$to_path/file_system_backup.tar.gz"

tar -zcvf $to_path $from_path
