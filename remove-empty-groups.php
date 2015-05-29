<?php

if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

require_once( 'cascade_ws/auth_user.php' );

$empty_groups = getEmptyGroups( $service );

if ( $empty_groups )
{
    foreach ( $empty_groups as $group_data )
	{
		$group_obj  = $group_data['obj'];
		$service->delete( $group_obj->getIdentifier() );
		echo "Removed " . $group_data['group'] . " group \r\n";
	}
}
else
{
    echo "No empty groups to delete! \r\n";
}