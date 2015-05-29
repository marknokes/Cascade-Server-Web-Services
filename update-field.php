<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

require_once( 'cascade_ws/auth_user.php' );

if ( isset( $argv ) && sizeof( $argv ) < 6 )
{
	echo "\r\n" . 'Error - 5 Args required.' . "\r\n" . "\r\n"; 
	echo 'System     [t,p] t = testing, p = production' . "\r\n";
	echo 'Site Name  Site name listed in Cascade Server' . "\r\n";
	echo 'Folder Id  Click folder in cascade copy string from URL after id=' . "\r\n";
	echo 'Node       Look in data def. Ex: banner;content2' . "\r\n";
	echo 'New Text   The text that will replace old text.' . "\r\n";
	exit;
}

$site_name 	= $argv[2];
$folder_id 	= $argv[3];
$node 		= $argv[4];
$new_text	= $argv[5];
$function 	= array( Page::TYPE => array( "assetTreeUpdatePage" ) );
$params 	= array( 
	'assetTreeUpdatePage' => array(
		'data' 	=> $new_text,
		'node'	=> $node
	)
);

$cascade->getAsset( Folder::TYPE, $folder_id, $site_name )->
	getAssetTree()->
		traverse( $function, $params );

echo "\r\n" . 'Update complete.' . "\r\n";