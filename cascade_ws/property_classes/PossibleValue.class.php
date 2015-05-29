<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class PossibleValue extends Property
{
    public function __construct( 
    	stdClass $v=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        // could be NULL for text
        if( $v != NULL )
        {
            if( $v->value == NULL || 
                $v->value == '' )
            {
                throw new EmptyValueException( "The value cannot be empty." );
            }
                
            if(    !BooleanValues::isBoolean( $v->selectedByDefault ) )
            {
                throw new UnacceptableValueException( "The value " . $v->selectedByDefault .
                    " must be a boolean" );
            }
            
            $this->value               = $v->value;
            $this->selected_by_default = $v->selectedByDefault;
        }
    }
    
    public function getSelectedByDefault()
    {
        return $this->selected_by_default;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setSelectedByDefault( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $value must be a boolean" );

        $this->selected_by_default = $bool;
        return $this;
    }
    
    public function toStdClass()
    {
        if( $this->value == NULL || $this->value == '' )
            throw new EmptyValueException( "The value cannot be empty." );
            
        $obj                    = new stdClass();
        $obj->value             = $this->value;
        $obj->selectedByDefault = $this->selected_by_default;
        return $obj;
    }

    private $selected_by_default;
    private $value;
}
?>
