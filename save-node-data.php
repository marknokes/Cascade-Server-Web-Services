<?php

error_reporting( E_ERROR );

if ( !isset( $argv ) ) exit; // Prevent browser access

$filename = "subsites_" . time() . ".txt";

$time = date("F j, Y, g:i a");

$which_system = isset( $argv[1] ) ? $argv[1] : false;

$node = isset( $argv[2] ) ? $argv[2] : false;

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");
     
if ( false === $node )
     die("Please specify node in the following format. parentNode;subnode");

require_once( 'cascade_ws/auth_user.php' );

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

$service->listSites();

if( $service->isSuccessful() )
{
    $assetIdentifiers = $service->getReply()->listSitesReturn->sites->assetIdentifier;

	$results = array();

    foreach( $assetIdentifiers as $assetIdentifier )
    {
    	$site_name = $assetIdentifier->path->path;

    	// Traverse the asset tree and save results to $results array
		$cascade->getSite( $site_name )->
			getAssetTree()->
				traverse(
					array(
						Page::TYPE => array(
							"getNodeData"
						)
					),
					$params = array(
						'getNodeData' => array(
							'node'	=> $node,
							'site'  => $site_name
						)
					),
					&$results
				);
    }

    // Open our file. This will contain the results, one per line.
    $file = fopen( $filename, "w" ) or die( "Unable to open file!" );

    // Iterate through results, echo in command line, and save to file.
    if ( $results )
    {
	    foreach( $results as $key => $value )
	    {
	    	if ( $key === 'results' )
	    	{
	    		foreach( $value as $top_level_site => $results )
	    		{
	    			write_msg( $file, $top_level_site . "\r\n\r\n" );

	    			foreach( $results as $nodeData )
	    				write_msg( $file, $nodeData . "\r\n" );
	    		}
	    	}
	    	elseif ( $key === 'exceptions' )
	    	{
	    		foreach( $value as $exception )
	    			write_msg( $file, $exception . "\r\n" );
	    	}
	    }
	}
	else
		write_msg( $file, 'Nothing here to see, folks' );

	// Just for fun
	write_msg( $file, 'Script Started: ' . $time . "\r\n" . 'Script Finished: ' . date("F j, Y, g:i a") );

	// Can't forget to close it!
    fclose( $file );
    
    write_msg( $file, $filename . ' saved.' );
}
else
    $service->printLastResponse();