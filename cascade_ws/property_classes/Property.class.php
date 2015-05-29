<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/28/2015 Changed signature of the constructor.
 */
abstract class Property
{
    public abstract function __construct( 
    	stdClass $obj=NULL,
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL
    );
    public abstract function toStdClass();
}
?>
