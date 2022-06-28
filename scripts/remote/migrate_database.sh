#! /bin/bash
# This file should migrate the database

. ~/scripts/create_state.sh

STATE_FILE="$(dirname $0)/migrate_database"

create_pending_file "$STATE_FILE"

{
  touch "test.txt"
} || {
  create_finish_failure_file "$STATE_FILE"
  exit
}

create_finish_success_file "$STATE_FILE"

