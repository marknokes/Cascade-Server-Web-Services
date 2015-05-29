<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class FieldValue extends Property
{
    public function __construct( 
    	stdClass $fv=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        $this->values = array();
        
        if( $fv != NULL )
        {
            if( isset( $fv->array ) )
            {
                $this->processValues( $fv );
            }
            else
            {
				if( isset( $fv->value ) )
                    $this->values[] = $fv->value;
            }
        }
    }
    
    public function getValues()
    {
        return $this->values; // can be NULL
    }
    
    public function setValues( $values ) // an array of stdClass objects
    {
        $this->values = array();
        
        $count = count( $values );
        
        if( $count == 1 ) // NULL or object
        {
            $this->values[] = $values[0]->value;
        }
        else
        {
            foreach( $values as $value )
            {
                if( $value->value == NULL || $value->value == '' )
                {
                    throw new EmptyValueException( "The value cannot be empty." );
                }
            
                if( in_array( $value->value, $this->values ) )
                {
                    throw new NonUniqueValueException( "The value " . $value->value . " already exist." );
                }
                else
                {
                    $this->values[] = $value->value;
                }
            }
        }
        
        return $this;
    }
    
    public function toStdClass()
    {
        $obj   = new stdClass();
        $count = count( $this->values );
        
        if( $count == 1 ) // NULL or 1 value
        {
            $value = new stdClass();
            
            if( $this->values[0] != '' )
            {
                $value->value = $this->values[0];
                $obj->fieldValue = $value;
            }
        }
        else // one or more
        {
            $obj->fieldValue = array();

            for( $i = 0; $i < $count; $i++ )
            {
                $value             = new stdClass();
                $value->value      = $this->values[$i];
                $obj->fieldValue[] = $value;
            }
        }

        return $obj;
    }
    
    // $values: 'array'=>an array of stdClass
    private function processValues( $values ) 
    {
        $values = $values->array; // now an array of stdClass
        
        foreach( $values as $value )
        {
            $this->values[] = $value->value;
        }
    }

    private $values;
}
?>
