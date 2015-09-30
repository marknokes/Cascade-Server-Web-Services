<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

require_once( 'cascade_ws_ns/auth_user.php' );

if ( isset( $argv ) && sizeof( $argv ) < 5 )
{
    echo PHP_EOL . 'Error - 4 Args required.' . PHP_EOL . PHP_EOL; 
	echo 'System        [t,p] t = testing, p = production' . PHP_EOL;
	echo 'Folder        Example: /administration/subfolder' . PHP_EOL;
	echo 'Site  	    Name of Cascade Server Website' . PHP_EOL;
	echo 'Exclude Self  true or false' . PHP_EOL;
	exit;
}

$results 		= array();

$folder 		= $argv[2];

$site 			= $argv[3];

$exclude_self 	= $argv[4];

$functions = array(
	cascade_ws_asset\File::TYPE => array( "assetTreeGetSubscribers" ),
	cascade_ws_asset\Page::TYPE => array( "assetTreeGetSubscribers" ),
	cascade_ws_asset\TextBlock::TYPE => array( "assetTreeGetSubscribers" ),
	cascade_ws_asset\XmlBlock::TYPE => array( "assetTreeGetSubscribers" )
);

$cascade->getAsset( cascade_ws_asset\Folder::TYPE, $folder, $site )->getAssetTree()->traverse( $functions, NULL, $results ); 


function exclude_self( $item, $key )
{
	global $folder;
	
	return false === strpos( $item, trim( $folder, "/" ) );
}

$doc = "";

foreach( $results as $page => $subscribers )
{

	if ( "true" == $exclude_self )
		$subscribers = array_filter( $subscribers, 'exclude_self', ARRAY_FILTER_USE_BOTH  );

	if ( $subscribers )
	{
		$doc .= "Asset: " . $page;
		$doc .= "\n\t";
		$doc .= implode( "\n\t", $subscribers );
		$doc .= PHP_EOL . PHP_EOL;
	}
}

$fp = fopen( time() . '.txt', 'w' );

fwrite( $fp, $doc );

fclose( $fp );