#! /bin/bash
# This file creates a backup from database
# how to use flags:
#   -u'username' -> here comes the username
#   -p'password' -> here comes the password
#   -h'hostName' -> here comes the host name
#   -d'database' -> here comes the name from the database
#   -t'path/to' -> here comes the path where the backup should be saved

. ~/scripts/create_state.sh

while getopts u:p:h:d:t: flag
do
  case "${flag}" in
    u) user=${OPTARG};;
    p) password=${OPTARG};;
    h) host=${OPTARG};;
    d) database=${OPTARG};;
    t) to_path=${OPTARG};;
  esac
done

STATE_FILE="$(dirname $0)/database_backup"

create_pending_file "$STATE_FILE"

final_path="$to_path/database"
mkdir -p "$final_path"

BACKUP_TEMP_FILE="$final_path/backup.sql"
BACKUP_FILE="$final_path/$(date +%s).tar.gz"
{
  mysqldump --column-statistics=0 -u "$user" -p"$password" -h"$host" "$database" > "$BACKUP_TEMP_FILE"
  if [ -d "$final_path" ]; then
    tar czf "$BACKUP_FILE" --directory="$final_path" .
    rm "$BACKUP_TEMP_FILE"
  fi
} || {
  rm "$BACKUP_TEMP_FILE"
  create_finish_failure_file "$STATE_FILE"
  exit
}

create_finish_success_file "$STATE_FILE"
