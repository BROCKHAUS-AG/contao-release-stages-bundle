#! /bin/bash
# This file should backup the file system

FROM_PATH=$1
TO_PATH="$2/file_system_backup.tar.gz"

tar -zcvf $TO_PATH $FROM_PATH
