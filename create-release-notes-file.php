<?php

$commit_message = getenv( "COMMITTEXT" );
$commit_message_parts = explode( "\n\n", $commit_message );
$commit_message_body = $commit_message_parts[1];
$commit_message_body_lines = explode( "\n", $commit_message_body );

$release_notes = '';

foreach( $commit_message_body_lines as $commit_message_body_line ){
    $release_notes .= '* ' . $commit_message_body_line . PHP_EOL;
}

file_put_contents( 'release_notes.txt', $release_notes );
exit;
