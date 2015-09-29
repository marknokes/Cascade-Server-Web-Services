<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/28/2015 Added namespaces.
  * 8/27/2014 Class created.
 */
namespace cascade_ws_utility;

use cascade_ws_constants as constants;
use cascade_ws_AOHS as aohs;
use cascade_ws_exception as exception;
use cascade_ws_asset as asset;

class AssetTest
{
    public function __construct( asset\Asset $a )
    {
    	if( $a == NULL )
    		throw new exception\TestException( "The asset is NULL." );
    	$this->asset   = $a;
    	$this->service = $a->getService();
    	$this->cascade = new asset\Cascade( $this->service );
    }
    
    public function assertEquals( $test, $value )
    {
    	if( is_string( $test ) && method_exists( $this->asset, $test ) )
    		$test = $this->asset->$test();
    
    	if( $test !== $value )
    		throw new exception\TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assetExceptionThrown( $test, $exception_name=NULL )
    {
    	if( !is_string( $test ) )
    		throw new exception\TestException( 
    			S_SPAN . "The first parameter must be a code string." . E_SPAN );
    		
    	try
    	{
    		eval( $test );
    	}
    	catch( \Exception $e )
    	{
    		if( is_string( $exception_name ) && $exception_name != "" && $exception_name != get_class( $e ) )
    		{
    			throw new exception\TestException( S_SPAN . "The " . __METHOD__ . 
    				" test failed: the exception class name $exception_name does not match " . 
    				get_class( $e ) . E_SPAN );
    		}
    		return;
    	}
    	throw new exception\TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assertIsInArray( $array, $value )
    {
    	if( is_string( $array ) && method_exists( $this->asset, $array ) )
    		$array = $this->asset->$array();
    		
    	if( !is_array( $array ) )
    		throw new exception\TestException( 
    			S_SPAN . "The first parameter should be an array." . E_SPAN );
    		
    	if( !in_array( $value, $array ) )
    		throw new exception\TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assertNotEquals( $test, $value )
    {
    	if( is_string( $test ) && method_exists( $this->asset, $test ) )
    		$test = $this->asset->$test();
    		
    	if( $test === $value || $test == $value )
    		throw new exception\TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assertGreaterThan( $test, $value )
    {
    	if( is_string( $test ) && method_exists( $this->asset, $test ) )
    		$test = $this->asset->$test();
    		
    	if( $test === $value || $test == $value || $test < $value )
    		throw new exception\TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assertLessThan( $test, $value )
    {
    	if( is_string( $test ) && method_exists( $this->asset, $test ) )
    		$test = $this->asset->$test();
    		
    	if( $test === $value || $test == $value || $test > $value )
    		throw new exception\TestException( "The " . __METHOD__ . " test failed." );
    }
    
    
    private $asset;
    private $cascade;
    private $service;
}