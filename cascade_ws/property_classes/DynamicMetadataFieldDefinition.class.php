<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 9/18/2014 Fixed bugs in appendValue and swapValue.
 */
class DynamicMetadataFieldDefinition extends Property
{
	const DEBUG = false;
	const DUMP  = false;
	
    public function __construct( 
    	stdClass $obj=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
    	if( isset( $obj ) )
    	{
			$this->name            = $obj->name;
			$this->label           = $obj->label;
			$this->field_type      = $obj->fieldType;
			$this->required        = $obj->required;
			$this->visibility      = $obj->visibility;
		
			// $obj->possibleValues->possibleValue can be NULL
			if( isset( $obj->possibleValues ) && isset( $obj->possibleValues->possibleValue ) )
				$this->processPossibleValues( $obj->possibleValues->possibleValue );
		}
    }
    
    public function appendValue( $value )
    {
        // type of text
        if( $this->possible_values == NULL )
        {
            echo M::TEXT_NO_POSSIBLE_VALUE . BR;
            return $this;
        }
    
        $value = trim( $value );
        
        if( $value == '' )
            throw new EmptyValueException( "The value cannot be empty." );
    
        if( !$this->hasPossibleValue( $value ) )
        {
        	if( self::DEBUG ) { DebugUtility::out( "Adding " . $value ); }
            $obj = new stdClass();
            $obj->value = $value;
            $obj->selectedByDefault  = false;
            $this->possible_values[] = new PossibleValue( $obj );
            $this->values[]          = $value;
            if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $this->values ); }
        }
        else
        {
            echo "The value $value already exists." . BR;
        }
        return $this;
    }
    
    public function getFieldType()
    {
        return $this->field_type;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPossibleValueStrings()
    {
        if( $this->possible_values == NULL )
        {
            echo M::TEXT_NO_POSSIBLE_VALUE . BR;
            return "";
        }

        return $this->values;
    }
    
    public function getRequired()
    {
        return $this->required;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function hasPossibleValue( $value )
    {
        if( $this->possible_values == NULL )
        {
            echo M::TEXT_NO_POSSIBLE_VALUE . BR;
            return false;
        }

        return in_array( $value, $this->values );
    }
    
    public function removeValue( $value )
    {
        // type of text
        if( $this->possible_values == NULL )
        {
            echo M::TEXT_NO_POSSIBLE_VALUE . BR;
            return $this;
        }
        
        if( $value == '' )
            throw new EmptyValueException( "The value cannot be empty." );
            
        if( !in_array( $value, $this->values ) )
            throw new NoSuchValueException( "The value $value does not exist." );
            
        $count = count( $this->possible_values );
    
        for( $i = 0; $i < $count; $i++ )
        {
            if( $this->possible_values[ $i ]->getValue() == $value )
            {
            	if( self::DEBUG ) { DebugUtility::out(  "Removing $value" ); }
                $before        = array_slice( $this->possible_values, 0, $i );
                $values_before = array_slice( $this->values, 0, $i );
                if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $values_before ); }
                
                $after         = array();
                $values_after  = array();
                
                if( $count - $i > 1 )
                {
                    $after  = array_slice( $this->possible_values, $i + 1 );
                    $values_after = array_slice( $this->values, $i + 1 );
                }
                
                $this->possible_values = array_merge( $before, $after );
                $this->values          = array_merge( $values_before, $values_after );
                break;
            }
        }

        return $this;
    }
    
    public function setLabel( $label )
    {
        $label = trim( $label );
        
        if( $label == '' )
            throw new EmptyValueException( "The label cannot be empty." );
        
        $this->label = $label;
        return $this;
    }
    
    public function setRequired( $required )
    {
        if( !BooleanValues::isBoolean( $required ) )
            throw new UnacceptableValueException( "The value $required must be a boolean" );
        
        if( $required )
        {
            $this->visibility = T::VISIBLE;
        }
        $this->required = $required;
        return $this;
    }
    
    public function setSelectedByDefault( $value )
    {
        if( !in_array( $value, $this->values ) )
            throw new NoSuchValueException( "The value $value does not exist" );
    
        foreach( $this->possible_values as $item )
        {
            // the relevant item
            if( $item->getValue() == $value )
            {
                $item->setSelectedByDefault( true );
            }
            // radio and dropdown
            else if( $this->field_type == T::RADIO || $this->field_type == T::DROPDOWN )
            {
                $item->setSelectedByDefault( false );
            }
        }
        
        return $this;
    }
    
    public function setVisibility( $visibility )
    {
        if( !VisibilityValues::isVisibility( $visibility ) )
            throw new UnacceptableValueException( "The value $visibility is not acceptable" );
    
        if( $visibility == T::HIDDEN )
        {
            $this->required = false;
            $this->visibility = $visibility;
        }
        else
        {
            $this->visibility = $visibility;
        }
    }
    
    public function swapValues( $value1, $value2 )
    {
        // type of text
        if( $this->possible_values == NULL )
        {
            echo M::TEXT_NO_POSSIBLE_VALUE . BR;
            return $this;
        }
    
        if( $value1 == '' || $value2 == '' )
            throw new EmptyValueException( "The value cannot be empty." );
            
        if( !in_array( $value1, $this->values ) )
            throw new NoSuchValueException( "The value $value1 does not exist" );
        
        if( !in_array( $value2, $this->values ) )
            throw new NoSuchValueException( "The value $value2 does not exist" );
            
        $first_pv_pos  = -1;
        $second_pv_pos = -1;
            
        $count = count( $this->possible_values );
    
        for( $i = 0; $i < $count; $i++ )
        {
            if( $this->possible_values[ $i ]->getValue() == $value1 )
            {
                $first_pv_pos = $i;
            }
            
            if( $this->possible_values[ $i ]->getValue() == $value2 )
            {
                $second_pv_pos = $i;
            }
        }
        
        $temp_value = $this->values[ $first_pv_pos ];
        $this->values[ $first_pv_pos ]  = $value2;
        $this->values[ $second_pv_pos ] = $value1;
        
        $temp = $this->possible_values[ $first_pv_pos ];
        $this->possible_values[ $first_pv_pos ] = $this->possible_values[ $second_pv_pos ];
        $this->possible_values[ $second_pv_pos ] = $temp;
        
        return $this;
    }
    
    public function toStdClass()
    {
        $obj                                = new stdClass();
        $obj->name                          = $this->name;
        $obj->label                         = $this->label;
        $obj->fieldType                     = $this->field_type;
        $obj->required                      = $this->required;
        $obj->visibility                    = $this->visibility;
        $obj->possibleValues                = new stdClass();
        $obj->possibleValues->possibleValue = array();
        
        if( $this->possible_values != NULL )
        {
            $count = count( $this->possible_values );
            
            if( $count == 1 )
            {
                $obj->possibleValues->possibleValue = 
                    $this->possible_values[0]->toStdClass();
            }
            else
            {
                $v_array        = array();
                $selected_count = 0;
                
                for( $i = 0; $i < $count; $i++ )
                {
                    $cur_value = $this->possible_values[ $i ]->getValue();
                    
                    if( $this->possible_values[ $i ]->getSelectedByDefault() )
                    {
                        $selected_count++;
                        
                        if( $selected_count > 1 && 
                            ( $this->field_type == T::RADIO || $this->field_type == T::DROPDOWN )
                        )
                        {
                            throw new MultipleSelectedByDefaultException( 
                                "Multiple values have been selected by default" );
                        }
                    }
                    
                    if( in_array( $cur_value, $v_array ) )
                    {
                        throw new NonUniqueValueException( "Repeated value " . " found" );
                    }
                    else
                    {
                        $v_array[] = $cur_value;
                    }
                
                    $obj->possibleValues->possibleValue[] = 
                        $this->possible_values[ $i ]->toStdClass();
                }
            }
        }
        else
        {
            $obj->possibleValues = new stdClass();
        }
        return $obj;
    }
    
    public function unsetSelectedByDefault( $value )
    {
        if( !$this->hasPossibleValue( $value ) )
            throw new NoSuchValueException( "The value $value does not exist" );
    
        foreach( $this->possible_values as $item )
        {
            // the relevant item
            if( $item->getValue() == $value )
            {
                $item->setSelectedByDefault( false );
            }
        }
        return $this;
    }
    
    private function processPossibleValues( $values )
    {
        $this->possible_values = array();

        if( $values == NULL ) // text
        {
            $this->possible_values = NULL;
            return;
        }
        
        if( !is_array( $values ) )
        {
            $values = array( $values );
        }
        
        $count = count( $values );
        
        for( $i = 0; $i < $count; $i++ )
        {
            $this->possible_values[] = new PossibleValue( $values[ $i ] );
            $this->values[] = $values[ $i ]->value;
        }
    }
    
    private $name;
    private $label;
    private $field_type;
    private $required;
    private $visibility;
    private $possible_values;
    private $values;
}
?>
