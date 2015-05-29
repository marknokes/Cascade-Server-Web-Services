<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Parameter extends Property
{
    public function __construct( 
    	stdClass $p=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        if( $p != NULL )
        {
            $this->name  = $p->name;
            $this->value = $p->value;
        }
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue( $value )
    {
        $this->value = $value;
        return $this;
    }
    
    public function toStdClass()
    {
        $obj        = new stdClass();
        $obj->name  = $this->name;
        $obj->value = $this->value;
        return $obj;
    }

    private $name;
    private $value;
}
?>
