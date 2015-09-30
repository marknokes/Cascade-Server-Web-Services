<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

if ( isset( $argv ) && sizeof( $argv ) < 3 )
{
	echo PHP_EOL . 'Error - 2 Args required.' . PHP_EOL . PHP_EOL; 
	echo 'System     [t,p] t = testing, p = production' . PHP_EOL;
	echo 'Folder Id  Click folder in cascade copy string from URL after id=' . PHP_EOL;
	exit;
}

require_once( 'cascade_ws_ns/auth_user.php' );

$results 	= array();

$folder_id 	= $argv[2];

$functions 	= array(
	cascade_ws_asset\File::TYPE => array( "assetTreeStore" ),
	cascade_ws_asset\Page::TYPE => array( "assetTreeStore" ),
	cascade_ws_asset\Folder::TYPE => array( "assetTreeStore" )
);

$cascade->getAsset( cascade_ws_asset\Folder::TYPE, $folder_id )->getAssetTree()->traverse( $functions, NULL, $results ); 

print_r( $results );