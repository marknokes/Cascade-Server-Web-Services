<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

require_once( 'cascade_ws/auth_user.php' );

if ( isset( $argv ) && sizeof( $argv ) < 3 )
{
	echo "\r\n" . 'Error - 2 Args required.' . "\r\n" . "\r\n"; 
	echo 'System     [t,p] t = testing, p = production' . "\r\n";
	echo 'Folder Id  Click folder in cascade copy string from URL after id=' . "\r\n";
	exit;
}

$results = array();

$folder_id = $argv[2];

$functions = array(
	File::TYPE => array( "assetTreeStore" ),
	Page::TYPE => array( "assetTreeStore" ),
	Folder::TYPE => array( "assetTreeStore" )
);

$cascade->getAsset( Folder::TYPE, $folder_id )->getAssetTree()->traverse( $functions, NULL, $results ); 

print_r( $results );