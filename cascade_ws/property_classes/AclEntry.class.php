<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class AclEntry extends Property
{
    public function __construct( 
    	stdClass $ae=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        if( $ae != NULL )
        {
            $this->level = $ae->level;
            $this->type  = $ae->type;
            $this->name  = $ae->name;
        }
    }
    
    public function display()
    {
        echo "Level: " . $this->level . BR .
             "Type: "  . $this->type . BR .
             "Name: "  . $this->name . BR . BR;
        return $this;
    }
    
    public function getLevel()
    {
        return $this->level;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setLevel( $level )
    {
        if( !LevelValues::isLevel( $level ) )
            throw new UnacceptableValueException( "The level $level is unacceptable." );
            
        $this->level = $level;
        return $this;
    }

    public function toStdClass()
    {
        $obj        = new stdClass();
        $obj->level = $this->level;
        $obj->type  = $this->type;
        $obj->name  = $this->name;
        return $obj;
    }
    
    private $level;
    private $type;
    private $name;
}
?>
