PENDING_FILE=".pending"
SUCCESS_FILE=".success"
FAILURE_FILE=".fail"

# parameter 1 should be the path
write_state()
{
  echo "" > $1
}


delete_file() {
  if [ -f "$1" ]; then
    rm "$1"
  fi
}

delete_old_files() {
  delete_file $1
  delete_file $2
  delete_file $3
}

# parameter 1 should be the path
create_pending_file() {
  delete_old_files "$1$PENDING_FILE" "$1$SUCCESS_FILE" "$1$FAILURE_FILE"
  write_state "$1$PENDING_FILE"
}

# parameter 1 should be the path
create_finish_success_file() {
  delete_file "$1$PENDING_FILE"
  write_state "$1$SUCCESS_FILE"
}

# parameter 1 should be the path
create_finish_failure_file() {
  delete_file "$1$PENDING_FILE"
  write_state "$1$FAILURE_FILE"
}

