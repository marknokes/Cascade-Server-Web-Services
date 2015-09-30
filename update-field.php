<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

require_once( 'cascade_ws_ns/auth_user.php' );

if ( isset( $argv ) && sizeof( $argv ) < 6 )
{
	echo PHP_EOL . 'Error - 5 Args required.' . PHP_EOL . PHP_EOL; 
	echo 'System     [t,p] t = testing, p = production' . PHP_EOL;
	echo 'Site Name  Site name listed in Cascade Server' . PHP_EOL;
	echo 'Folder Id  Click folder in cascade copy string from URL after id=' . PHP_EOL;
	echo 'Node       Look in data def. Ex: banner;content2' . PHP_EOL;
	echo 'New Text   The text that will replace old text.' . PHP_EOL;
	exit;
}

$site_name 	= $argv[2];
$folder_id 	= $argv[3];
$node 		= $argv[4];
$new_text	= $argv[5];
$function 	= array( cascade_ws_asset\Page::TYPE => array( "assetTreeUpdatePage" ) );
$params 	= array( 
	'assetTreeUpdatePage' => array(
		'data' 	=> $new_text,
		'node'	=> $node
	)
);

$cascade->getAsset( cascade_ws_asset\Folder::TYPE, $folder_id, $site_name )->
	getAssetTree()->
		traverse( $function, $params );

echo PHP_EOL . 'Update complete.' . PHP_EOL;