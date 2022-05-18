PENDING_FILE=".pending"
SUCCESS_FILE=".success"
FAILURE_FILE=".fail"

# parameter 1 is the path
write_state()
{
  echo "" > $1
}

# parameter 1 is the path
create_pending_file() {
  write_state "$1$PENDING_FILE"
}

# parameter 1 is the path
create_finish_success_file() {
  if [ ! -f "$1$FAILURE_FILE" ]; then
    rm "$1$PENDING_FILE"
    write_state "$1$SUCCESS_FILE"
  fi
}

# parameter 1 is the path
create_finish_failure_file() {
  rm "$1$PENDING_FILE"
  write_state "$1$FAILURE_FILE"
}

