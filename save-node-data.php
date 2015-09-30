<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$filename 		= "subsites_" . time() . ".txt";

$time 			= date("F j, Y, g:i a");

$which_system 	= isset( $argv[1] ) ? $argv[1] : false;

$node 			= isset( $argv[2] ) ? $argv[2] : false;

$site_name 		= isset( $argv[3] ) ? $argv[3] : false;

$messages 		= array();

if ( false === $which_system )
    $messages[] = "Arg 1 should be system. t or p for testing or production respectively.";
if ( false === $node )
	$messages[] = "Arg 2 should be node in the following format. parentNode;subnode";
if ( false === $site_name )
	$messages[] = "Arg 3 should be website name from Cascade Server. Ex: ACM";

if ( $messages ) {
	foreach( $messages as $message )
		echo $message . PHP_EOL;
	die();
}

require_once( 'cascade_ws_ns/auth_user.php' );

/*
* @param file pointer $file File pointer created with fopen().
* @param str $msg A string to echo in cmd and write to file.
*
* @return null
*/
function write_msg( $file, $msg )
{
	echo $msg;
	fwrite( $file, $msg );
}

$fp = fopen( $filename, "w" );

// Traverse the asset tree and save results to $results array
$cascade->getSite( $site_name )->
	getAssetTree()->
		traverse(
			array(
				cascade_ws_asset\Page::TYPE => array(
					"saveNodeData"
				)
			),
			$params = array(
				'saveNodeData' => array(
					'node'	=> $node,
					'fp' 	=> $fp
				)
			)
		);

fclose( $fp );