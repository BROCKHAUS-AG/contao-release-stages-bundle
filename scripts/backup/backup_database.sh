#! /bin/bash
# This file creates a backup from database
# how to use flags:
#   -u'username' -> here comes the username
#   -p'password' -> here comes the password
#   -h'hostName' -> here comes the host name
#   -d'database' -> here comes the name from the database

. ~/scripts/create_state.sh

while getopts u:p:h:d: flag
do
  case "${flag}" in
    u) user=${OPTARG};;
    p) password=${OPTARG};;
    h) host=${OPTARG};;
    d) database=${OPTARG};;
  esac
done

STATE_FILE="$(dirname $0)/database_backup"

create_pending_file "$STATE_FILE"

BACKUP_FILE="$(dirname $0)/database_backup.sql"
{
  mysqldump --opt --no-tablespaces -u "$user" -p"$password" -h"$host" "$database" > "$BACKUP_FILE"
} || {
  create_finish_failure_file "$STATE_FILE"
}

create_finish_success_file "$STATE_FILE"
