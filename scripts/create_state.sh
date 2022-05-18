PENDING_FILE=".pending"
SUCCESS_FILE=".success"
FAILURE_FILE=".fail"

# parameter 1 should be the path
write_state()
{
  echo "" > $1
}

delete_old_files(){
  rm -f "$1$PENDING_FILE"
  rm -f "$1$SUCCESS_FILE"
  rm -f "$1$FAILURE_FILE"
}

# parameter 1 should be the path
create_pending_file() {
  delete_old_files
  write_state "$1$PENDING_FILE"
}

# parameter 1 should be the path
create_finish_success_file() {
  if [ ! -f "$1$FAILURE_FILE" ]; then
    rm -f "$1$PENDING_FILE"
    write_state "$1$SUCCESS_FILE"
  fi
}

# parameter 1 should be the path
create_finish_failure_file() {
  rm -f "$1$PENDING_FILE"
  write_state "$1$FAILURE_FILE"
}

