#! /bin/bash
# This file creates a backup from database
# how to use flags:
#   -u'username' -> here comes the username
#   -p'password' -> here comes the password
#   -h'hostName' -> here comes the host name
#   -d'database' -> here comes the name from the database
#   -f'path/to/backup/folder' -> here comes the path to the backup folder

while getopts u:p:h:d:f: flag
do
  case "${flag}" in
    u) user=${OPTARG};;
    p) password=${OPTARG};;
    h) host=${OPTARG};;
    d) database=${OPTARG};;
    f) path=${OPTARG};;
  esac
done

mkdir -p "$to_path"

to_path="$to_path/database_backup.sql"

mysqldump --opt --no-tablespaces -u "$user" -p"$path" -h"$host" "$database" > "$path"
