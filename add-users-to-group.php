<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system 	= isset( $argv[1] ) ? $argv[1] : false;

$group 			= isset( $argv[2] ) ? $argv[2] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");
     
if ( false === $group )
     die("Please specify group");

require_once( 'cascade_ws_ns/auth_user.php' );

$users_added = addAllUsersToGroup( $service, $group );

foreach( $users_added as $username )
    echo $username . " added to " . $group . PHP_EOL;