#! /bin/bash
# This file creates a backup from database
# how to use flags:
#   -u'username' -> here comes the username
#   -p'password' -> here comes the password
#   -h'hostName' -> here comes the host name
#   -d'database' -> here comes the name from the database
#   -t'path/to' -> here comes the path where the backup should be saved

while getopts i:u:p:h:P:d:t: flag
do
  case "${flag}" in
    i) ignoredTables=${OPTARG};;
    u) user=${OPTARG};;
    p) password=${OPTARG};;
    h) host=${OPTARG};;
    P) port=${OPTARG};;
    d) database=${OPTARG};;
    t) to_path=${OPTARG};;
  esac
done

rm -d -r -f "$to_path"
mkdir -p "$to_path"
chmod -R 0777 "$to_path"

BACKUP_TEMP_FILE="$to_path/database_migration.sql"

mysqldump --no-tablespaces "$ignoredTables" -u "$user" -p"$password" -h"$host" -P"$port" "$database" > "$BACKUP_TEMP_FILE"

# Das hier ist der exit Code vom vorherigen Befehl
exitCode=$?
exit $exitCode