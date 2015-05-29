<?php

if ( !isset( $argv ) ) exit; // Prevent browser access

$wwwrootPath = "/inetpub/wwwroot/";

$site = 'Cascade Server Site Name';

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

require_once( 'cascade_ws/auth_user.php' );

$server_dirs = array_filter( glob( $wwwrootPath . '*' ), 'is_dir' );

$wcms_directories = array();

foreach( $server_dirs as &$dir )
{
	$dir = str_replace( $wwwrootPath, '', $dir );

	try
	{
		$asset = Asset::getAsset( $service, Folder::TYPE, $dir, $site );

		if ( $path = $asset->getPath() )
			$wcms_directories[] = $path;
	}
	catch( NullAssetException $e )
	{

	}
}

$diff = array_diff( $server_dirs, $wcms_directories );

foreach( $diff as $diff_dir )
{
	echo $diff_dir . "\r\n";
}

echo "\r\n" . 'Total Directories: ' . count( $diff ) . "\r\n";