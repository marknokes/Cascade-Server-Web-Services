<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system 	= isset( $argv[1] ) ? $argv[1] : false;

$wwwrootPath 	= $argv[2];

$site 			= $argv[3];

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

if ( isset( $argv ) && sizeof( $argv ) < 4 )
{
	echo PHP_EOL . 'Error - 3 Args required.' . PHP_EOL . PHP_EOL; 
	echo 'System     [t,p] t = testing, p = production' . PHP_EOL;
	echo 'www root   Ex: /inetpub/wwwroot/' . PHP_EOL;
	echo 'Site       Name of the site in Cascade. Ex: "Some Site Name"' . PHP_EOL;
	exit;
}

require_once( 'cascade_ws_ns/auth_user.php' );

$server_dirs 		= array_filter( glob( $wwwrootPath . '*' ), 'is_dir' );

$wcms_directories 	= array();

foreach( $server_dirs as &$dir )
{
	$dir = str_replace( $wwwrootPath, '', $dir );

	try
	{
		$asset = cascade_ws_asset\Asset::getAsset( $service, cascade_ws_asset\Folder::TYPE, $dir, $site );

		if ( $path = $asset->getPath() )
			$wcms_directories[] = $path;
	}
	catch( cascade_ws_exception\NullAssetException $e )
	{
		continue;
	}
}

$diff = array_diff( $server_dirs, $wcms_directories );

foreach( $diff as $diff_dir )
	echo $diff_dir . PHP_EOL;

echo PHP_EOL . 'Total Directories: ' . count( $diff ) . PHP_EOL;