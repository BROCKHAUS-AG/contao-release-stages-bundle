#! /bin/bash
# This file should migrate the database
# how to use flags:
#   -u'username' -> here comes the username
#   -p'password' -> here comes the password
#   -h'hostName' -> here comes the host name
#   -d'database' -> here comes the name from the database
#   -f'path/from' -> here comes the path where the migration file is placed

basedir=$BASH_SOURCE
cd "$(dirname "$basedir")"

. create_state.sh


while getopts u:p:h:d:f: flag
do
  case "${flag}" in
    u) user=${OPTARG};;
    p) password=${OPTARG};;
    h) host=${OPTARG};;
    d) database=${OPTARG};;
    f) from_path=${OPTARG};;
  esac
done

STATE_FILE="$(dirname $0)/migrate_database"

create_pending_file "$STATE_FILE"

{
  mysql -u"$user" -p"$password" -h"$host" "$database" < "$from_path" --force
} || {
  create_finish_failure_file "$STATE_FILE"
  exit
}

create_finish_success_file "$STATE_FILE"

