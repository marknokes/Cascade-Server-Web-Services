<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 8/27/2014 Class created.
 */

class AssetTest
{
    public function __construct( Asset $a )
    {
    	if( $a == NULL )
    		throw new TestException( "The asset is NULL." );
    	$this->asset   = $a;
    	$this->service = $a->getService();
    	$this->cascade = new Cascade( $this->service );
    }
    
    public function assertEquals( $test, $value )
    {
    	if( is_string( $test ) && method_exists( $this->asset, $test ) )
    		$test = $this->asset->$test();
    
    	if( $test !== $value )
    		throw new TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assetExceptionThrown( $test, $exception_name=NULL )
    {
    	if( !is_string( $test ) )
    		throw new TestException( 
    			"<span style='color:red;font-weight:bold;'>The first parameter must be a code string." .
    			"</span>" );
    		
    	try
    	{
    		eval( $test );
    	}
    	catch( Exception $e )
    	{
    		if( is_string( $exception_name ) && $exception_name != "" && $exception_name != get_class( $e ) )
    		{
    			throw new TestException( "<span style='color:red;font-weight:bold;'>The " . __METHOD__ . 
    				" test failed: the exception class name $exception_name does not match " . 
    				get_class( $e ) . "</span>" );
    		}
    		return;
    	}
    	throw new TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assertIsInArray( $array, $value )
    {
    	if( is_string( $array ) && method_exists( $this->asset, $array ) )
    		$array = $this->asset->$array();
    		
    	if( !is_array( $array ) )
    		throw new TestException( 
    			"<span style='color:red;font-weight:bold;'>The first parameter should be an array." .
    			"</span>" );
    		
    	if( !in_array( $value, $array ) )
    		throw new TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assertNotEquals( $test, $value )
    {
    	if( is_string( $test ) && method_exists( $this->asset, $test ) )
    		$test = $this->asset->$test();
    		
    	if( $test === $value || $test == $value )
    		throw new TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assertGreaterThan( $test, $value )
    {
    	if( is_string( $test ) && method_exists( $this->asset, $test ) )
    		$test = $this->asset->$test();
    		
    	if( $test === $value || $test == $value || $test < $value )
    		throw new TestException( "The " . __METHOD__ . " test failed." );
    }
    
    public function assertLessThan( $test, $value )
    {
    	if( is_string( $test ) && method_exists( $this->asset, $test ) )
    		$test = $this->asset->$test();
    		
    	if( $test === $value || $test == $value || $test > $value )
    		throw new TestException( "The " . __METHOD__ . " test failed." );
    }
    
    
    private $asset;
    private $cascade;
    private $service;
}