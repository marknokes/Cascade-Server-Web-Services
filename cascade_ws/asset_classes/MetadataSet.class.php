<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 9/18/2014 Added a call to edit in a few methods
  * 7/14/2014 Added getDynamicMetadataFieldDefinitionsStdClass and setDynamicMetadataFieldDefinitions.
  * 7/7/2014 Added addField and fixed some bugs.
 */
class MetadataSet extends ContainedAsset
{
    const DEBUG    = false;
    const DUMP     = false;
    const TYPE     = T::METADATASET;
    const HIDDEN   = T::HIDDEN;
    const INLINE   = T::INLINE;
    const VISIBLE  = T::VISIBLE;
    
    const AUTHOR      = "author";
    const DESCRIPTION = "description";
    const DISPLAYNAME = "display-name";
    const KEYWORDS    = "keywords";
    const SUMMARY     = "summary";
    const TEASER      = "teaser";
    const TITLE       = "title";
    
    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        if( 
        	isset( $this->getProperty()->dynamicMetadataFieldDefinitions ) &&
            isset( $this->getProperty()->dynamicMetadataFieldDefinitions->dynamicMetadataFieldDefinition ) )
        {
            $this->processDynamicMetadataFieldDefinition();
        }
    }
    
    public function addDynamicFieldDefinition( $field_name, $type, $label, 
    	$required=false, $visibility=T::VISIBLE, $possible_values="" )
    {
    	if( $this->hasDynamicMetadataFieldDefinition( $field_name ) )
    	{
    		throw new Exception( "The dynamic field definition $field_name already exists. " );
    	}
    	
		if( $type != T::TEXT && trim( $possible_values ) == "" )
		{
			throw new EmptyValueException( EMPTY_POSSIBLE_VALUES );
		}
		
    	$dmfd = AssetTemplate::getDynamicMetadataFieldDefinition();
		$dmfd->dynamicMetadataFieldDefinition->name       = $field_name;
		$dmfd->dynamicMetadataFieldDefinition->label      = $label;
		$dmfd->dynamicMetadataFieldDefinition->fieldType  = $type;
		$dmfd->dynamicMetadataFieldDefinition->required   = $required;
		$dmfd->dynamicMetadataFieldDefinition->visibility = $visibility;
		
		if( $type != T::TEXT )
		{
			$dmfd->dynamicMetadataFieldDefinition->possibleValues = new stdClass();
			$values      = StringUtility::getExplodedStringArray( ";", $possible_values );
			$value_count = count( $values );
			
			if( $value_count == 1 )
			{
				$pv                    = new stdClass();
				$pv->value             = $values[ 0 ];
				$pv->selectedByDefault = false;
				
				$dmfd->dynamicMetadataFieldDefinition->possibleValues->possibleValue = $pv;
			}
			else
			{
				$dmfd->dynamicMetadataFieldDefinition->possibleValues->possibleValue = array();
				
				foreach( $values as $value )
				{
					if( self::DEBUG ) { DebugUtility::out( $value ); }
				
					$pv                    = new stdClass();
					$pv->value             = $value;
					$pv->selectedByDefault = false;
					
					$dmfd->dynamicMetadataFieldDefinition->possibleValues->possibleValue[] = $pv;
				}
			}
		}
		if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $dmfd ); }
		
		$dmfd_obj = new DynamicMetadataFieldDefinition( $dmfd->dynamicMetadataFieldDefinition );
		
    	$this->dynamic_metadata_field_definitions[] = $dmfd_obj;
    	
		if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $dmfd_obj->toStdClass() ); }
    	
    	$this->edit();
    	$this->processDynamicMetadataFieldDefinition();
    	
    	return $this;
    }
    
    public function addField( $field_name, $type, $label, 
    	$required=false, $visibility=T::VISIBLE, $possible_values="" )
    {
    	return $this->addDynamicFieldDefinition( $field_name, $type, $label, $required, $visibility, $possible_values );
    }
    
    /**
     * Appends a value/item to the end of a field.
     */
    public function appendValue( $name, $value )
    {
        $value = trim( $value );
        
        if( $value == '' )
            throw new EmptyValueException( "The value cannot be empty." );
            
        $def = $this->getDynamicMetadataFieldDefinition( $name );
        $def->appendValue( $value );
        $this->edit();
    	$this->processDynamicMetadataFieldDefinition();

        return $this;
    }
       
    public function edit()
    {
        $asset = new stdClass();
        
        $metadata_set = $this->getProperty();
        $metadata_set->dynamicMetadataFieldDefinitions->
            dynamicMetadataFieldDefinition = array();
            
        $count = $this->dynamic_metadata_field_definitions;
        
        if( $count > 0 )
			foreach( $this->dynamic_metadata_field_definitions as $definition )
			{
				$metadata_set->dynamicMetadataFieldDefinitions->
					dynamicMetadataFieldDefinition[] = $definition->toStdClass();
			}
        
        $asset->{ $p = $this->getPropertyName() } = $metadata_set;
        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
            throw new EditingFailureException( 
                "Failed to edit the asset. " . $service->getMessage() );
        }
        return $this->reloadProperty();
    }
    
    public function getAuthorFieldRequired()
    {
        return $this->getProperty()->authorFieldRequired;
    }
    
    public function getAuthorFieldVisibility()
    {
        return $this->getProperty()->authorFieldVisibility;
    }
    
    public function getDescriptionFieldRequired()
    {
        return $this->getProperty()->descriptionFieldRequired;
    }
    
    public function getDescriptionFieldVisibility()
    {
        return $this->getProperty()->descriptionFieldVisibility;
    }
    
    public function getDisplayNameFieldRequired()
    {
        return $this->getProperty()->displayNameFieldRequired;
    }
    
    public function getDisplayNameFieldVisibility()
    {
        return $this->getProperty()->displayNameFieldVisibility;
    }
    
    public function getDynamicMetadataFieldDefinition( $name )
    {
        if( !$this->hasDynamicMetadataFieldDefinition( $name ) )
            throw new NoSuchMetadataFieldDefinitionException( 
                "The definition $name does not exist" );
        
        foreach( $this->dynamic_metadata_field_definitions as $definition )
        {
            if( $definition->getName() == $name )
                return $definition;
        }
    }
    
    public function getDynamicMetadataFieldDefinitionNames()
    {
        return $this->field_names;
    }
    
    public function getDynamicMetadataFieldDefinitionsStdClass()
    {
    	return $this->getProperty()->dynamicMetadataFieldDefinitions;
    }
    
    public function getDynamicMetadataFieldPossibleValueStrings( $name )
    {
        if( !$this->hasDynamicMetadataFieldDefinition( $name ) )
            throw new NoSuchMetadataFieldDefinitionException( 
                "The definition $name does not exist" );
                
        foreach( $this->dynamic_metadata_field_definitions as $definition )
        {
            if( $definition->getName() == $name )
                return $definition->getPossibleValueStrings();
        }
    }

    public function getEndDateFieldRequired()
    {
        return $this->getProperty()->endDateFieldRequired;
    }
    
    public function getEndDateFieldVisibility()
    {
        return $this->getProperty()->endDateFieldVisibility;
    }
    
    public function getKeywordsFieldRequired()
    {
        return $this->getProperty()->keywordsFieldRequired;
    }
    
    public function getKeywordsFieldVisibility()
    {
        return $this->getProperty()->keywordsFieldVisibility;
    }
    
    // used by WordPressConnector
    public function getNonHiddenWiredFieldNames()
    {
        $fields = array();
        
        if( $this->getProperty()->authorFieldVisibility != self::HIDDEN )
            $fields[] = self::AUTHOR;
        if( $this->getProperty()->descriptionFieldVisibility != self::HIDDEN )
            $fields[] = self::DESCRIPTION;
        if( $this->getProperty()->displayNameFieldVisibility != self::HIDDEN )
            $fields[] = self::DISPLAYNAME;
        if( $this->getProperty()->keywordsFieldVisibility != self::HIDDEN )
            $fields[] = self::KEYWORDS;
        if( $this->getProperty()->summaryFieldVisibility != self::HIDDEN )
            $fields[] = self::SUMMARY;
        if( $this->getProperty()->teaserFieldVisibility != self::HIDDEN )
            $fields[] = self::TEASER;
        if( $this->getProperty()->titleFieldVisibility != self::HIDDEN )
            $fields[] = self::TITLE;
            
        return $fields;
    }
    
    public function getReviewDateFieldRequired()
    {
        return $this->getProperty()->reviewDateFieldRequired;
    }
    
    public function getReviewDateFieldVisibility()
    {
        return $this->getProperty()->reviewDateFieldVisibility;
    }
    
    public function getStartDateFieldRequired()
    {
        return $this->getProperty()->startDateFieldRequired;
    }
    
    public function getStartDateFieldVisibility()
    {
        return $this->getProperty()->startDateFieldVisibility;
    }
    
    public function getSummaryFieldRequired()
    {
        return $this->getProperty()->summaryFieldRequired;
    }
    
    public function getSummaryFieldVisibility()
    {
        return $this->getProperty()->summaryFieldVisibility;
    }
    
    public function getTeaserFieldRequired()
    {
        return $this->getProperty()->teaserFieldRequired;
    }
    
    public function getTeaserFieldVisibility()
    {
        return $this->getProperty()->teaserFieldVisibility;
    }
    
    public function getTitleFieldRequired()
    {
        return $this->getProperty()->titleFieldRequired;
    }
    
    public function getTitleFieldVisibility()
    {
        return $this->getProperty()->titleFieldVisibility;
    }
    
    public function hasDynamicMetadataFieldDefinition( $name )
    {
    	if( !is_array( $this->field_names ) )
    	{
    		return false;
    	}
        return in_array( $name, $this->field_names );
    }
    
    public function removeDynamicMetadataFieldDefinition( $name )
    {
        if( !in_array( $name, $this->field_names ) )
        {
            throw new NoSuchFieldException( "The field $name does not exist." );
        }
        
        $count = count( $this->dynamic_metadata_field_definitions );
        
        for( $i = 0; $i < $count; $i++ )
        {
            if( $this->dynamic_metadata_field_definitions[ $i ]->getName() == $name )
            {
                $before       = array_slice( $this->dynamic_metadata_field_definitions, 0, $i );
                $names_before = array_slice( $this->field_names, 0, $i );
                $after        = array();
                $names_after  = array();
                
                if( $count - $i > 1 )
                {
                    $after       = array_slice( $this->dynamic_metadata_field_definitions, $i + 1 );
                    $names_after = array_slice( $this->field_names, $i + 1 );
                }
                $this->dynamic_metadata_field_definitions = array_merge( $before, $after );
                $this->field_names = array_merge( $names_before, $names_after );
                break;
            }
        }
        $this->edit();
    	$this->processDynamicMetadataFieldDefinition();

        return $this;
    }
    
    public function removeValue( $name, $value )
    {
        $value = trim( $value );
        
        if( $value == '' )
            throw new EmptyValueException( "The value cannot be empty." );
            
        $def = $this->getDynamicMetadataFieldDefinition( $name );
        $def->removeValue( $value );
        $this->edit();
    	$this->processDynamicMetadataFieldDefinition();

        return $this;
    }
    
    public function setAuthorFieldRequired( $author_field_required=false )
    {
        if( !BooleanValues::isBoolean( $author_field_required ) )
            throw new UnacceptableValueException( "The value $author_field_required must be a boolean" );
            
        $this->getProperty()->authorFieldRequired = $author_field_required;
        return $this;
    }
    
    public function setAuthorFieldVisibility( $author_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $author_field_visibility ) )
            throw new UnacceptableValueException( "The value $author_field_visibility is not acceptable" );

        $this->getProperty()->authorFieldVisibility = $author_field_visibility;
        return $this;
    }
    
    public function setDescriptionFieldRequired( $description_field_required=false )
    {
        if( !BooleanValues::isBoolean( $description_field_required ) )
            throw new UnacceptableValueException( "The value $description_field_required must be a boolean" );
            
        $this->getProperty()->descriptionFieldRequired = $description_field_required;
        return $this;
    }
    
    public function setDescriptionFieldVisibility( $description_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $description_field_visibility ) )
            throw new UnacceptableValueException( "The value $description_field_visibility is not acceptable" );
        
        $this->getProperty()->descriptionFieldVisibility = $description_field_visibility;
        return $this;
    }
    
    public function setDisplayNameFieldRequired( $display_name_field_required=false )
    {
        if( !BooleanValues::isBoolean( $display_name_field_required ) )
            throw new UnacceptableValueException( "The value $display_name_field_required must be a boolean" );
            
        $this->getProperty()->displayNameFieldRequired = $display_name_field_required;
        return $this;
    }
    
    public function setDisplayNameFieldVisibility( $display_name_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $display_name_field_visibility ) )
            throw new UnacceptableValueException( "The value $display_name_field_visibility is not acceptable" );
        
        $this->getProperty()->displayNameFieldVisibility = $display_name_field_visibility;
        return $this;
    }
    
    public function setDynamicMetadataFieldDefinitions( stdClass $dmfd=NULL )
    {
    	if( $dmfd == NULL || $dmfd->dynamicMetadataFieldDefinition == NULL )
    	{
    		$this->getProperty()->dynamicMetadataFieldDefinitions = new stdClass();
    	}
    	else
    	{
			$this->dynamic_metadata_field_definitions = array();
			$this->field_names                        = array();

			$definitions = $dmfd->dynamicMetadataFieldDefinition;
			
			if( !is_array( $definitions ) )
			{
				$definitions = array( $definitions );
			}
		
			$count = count( $definitions );
		
			for( $i = 0; $i < $count; $i++ )
			{
				$this->dynamic_metadata_field_definitions[] = 
					new DynamicMetadataFieldDefinition( $definitions[ $i ] );
				$this->field_names[] = $definitions[ $i ]->name;
			}
		}
    	return $this->edit();
    }
    
    public function setEndDateFieldRequired( $end_date_field_required=false )
    {
        if( !BooleanValues::isBoolean( $end_date_field_required ) )
            throw new UnacceptableValueException( "The value $end_date_field_required must be a boolean" );
            
        $this->getProperty()->endDateFieldRequired = $end_date_field_required;
        return $this;
    }
    
    public function setEndDateFieldVisibility( $end_date_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $end_date_field_visibility ) )
            throw new UnacceptableValueException( "The value $end_date_field_visibility is not acceptable" );
        
        $this->getProperty()->endDateFieldVisibility = $end_date_field_visibility;
        return $this;
    }
    
    public function setKeywordsFieldRequired( $keywords_field_required=false )
    {
        if( !BooleanValues::isBoolean( $keywords_field_required ) )
            throw new UnacceptableValueException( "The value $keywords_field_required must be a boolean" );
            
        $this->getProperty()->keywordsFieldRequired = $keywords_field_required;
        return $this;
    }
    
    public function setKeywordsFieldVisibility( $keywords_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $keywords_field_visibility ) )
            throw new UnacceptableValueException( "The value $keywords_field_visibility is not acceptable" );
        
        $this->getProperty()->keywordsFieldVisibility = $keywords_field_visibility;
        return $this;
    }
    
    public function setLabel( $name, $label )
    {
        $label = trim( $label );
        
        if( $label == '' )
            throw new EmptyValueException( "The label cannot be empty." );
    
        if( $this->hasDynamicMetadataFieldDefinition( $name ) )
        {
            $d = $this->getDynamicMetadataFieldDefinition( $name );
            $d->setLabel( $label );
            
            return $this;
        }
        else
        {
            throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
        }
    }
    
    public function setRequired( $name, $required )
    {
        if( !BooleanValues::isBoolean( $required ) )
            throw new UnacceptableValueException( "The value $required must be a boolean" );
            
        if( $this->hasDynamicMetadataFieldDefinition( $name ) )
        {
            $d = $this->getDynamicMetadataFieldDefinition( $name );
            $d->setRequired( $required );
            
            return $this;
        }
        else
        {
            throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
        }
    }
    
    public function setReviewDateFieldRequired( $review_date_field_required=false )
    {
        if( !BooleanValues::isBoolean( $review_date_field_required ) )
            throw new UnacceptableValueException( "The value $review_date_field_required must be a boolean" );
            
        $this->getProperty()->reviewDateFieldRequired = $review_date_field_required;
        return $this;
    }
    
    public function setReviewDateFieldVisibility( $review_date_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $review_date_field_visibility ) )
            throw new UnacceptableValueException( "The value $review_date_field_visibility is not acceptable" );
        
        $this->getProperty()->reviewDateFieldVisibility = $review_date_field_visibility;
        return $this;
    }
    
    public function setSelectedByDefault( $name, $value )
    {
        $value = trim( $value );
        
        if( $value == '' )
            throw new EmptyValueException( "The value cannot be empty." );
    
        if( $this->hasDynamicMetadataFieldDefinition( $name ) )
        {
            $d = $this->getDynamicMetadataFieldDefinition( $name );
            
            if( $d->hasPossibleValue( $value ) )
            {
                $d->setSelectedByDefault( $value );
            }
        }
        else
        {
            throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
        }
            
        return $this;
    }
    
    public function setStartDateFieldRequired( $start_date_field_required=false )
    {
        if( !BooleanValues::isBoolean( $start_date_field_required ) )
            throw new UnacceptableValueException( "The value $start_date_field_required must be a boolean" );
            
        $this->getProperty()->startDateFieldRequired = $start_date_field_required;
        return $this;
    }
    
    public function setStartDateFieldVisibility( $start_date_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $start_date_field_visibility ) )
            throw new UnacceptableValueException( "The value $start_date_field_visibility is not acceptable" );
        
        $this->getProperty()->startDateFieldVisibility = $start_date_field_visibility;
        return $this;
    }
    
    public function setSummaryFieldRequired( $summary_field_required=false )
    {
        if( !BooleanValues::isBoolean( $summary_field_required ) )
            throw new UnacceptableValueException( "The value $summary_field_required must be a boolean" );
            
        $this->getProperty()->summaryFieldRequired = $summary_field_required;
        return $this;
    }
    
    public function setSummaryFieldVisibility( $summary_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $summary_field_visibility ) )
            throw new UnacceptableValueException( "The value $summary_field_visibility is not acceptable" );
        
        $this->getProperty()->summaryFieldVisibility = $summary_field_visibility;
        return $this;
    }
    
    public function setTeaserFieldRequired( $teaser_field_required=false )
    {
        if( !BooleanValues::isBoolean( $teaser_field_required ) )
            throw new UnacceptableValueException( "The value $teaser_field_required must be a boolean" );
            
        $this->getProperty()->teaserFieldRequired = $teaser_field_required;
        return $this;
    }
    
    public function setTeaserFieldVisibility( $teaser_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $teaser_field_visibility ) )
            throw new UnacceptableValueException( "The value $teaser_field_visibility is not acceptable" );
        
        $this->getProperty()->teaserFieldVisibility = $teaser_field_visibility;
        return $this;
    }
    
    public function setTitleFieldRequired( $title_field_required=false )
    {
        if( !BooleanValues::isBoolean( $title_field_required ) )
            throw new UnacceptableValueException( "The value $title_field_required must be a boolean" );
            
        $this->getProperty()->titleFieldRequired = $title_field_required;
        return $this;
    }
    
    public function setTitleFieldVisibility( $title_field_visibility=self::HIDDEN )
    {
        if( !VisibilityValues::isVisibility( $title_field_visibility ) )
            throw new UnacceptableValueException( "The value $title_field_visibility is not acceptable" );
        
        $this->getProperty()->titleFieldVisibility = $title_field_visibility;
        return $this;
    }
    
    public function setVisibility( $name, $visibility )
    {
        if( !VisibilityValues::isVisibility( $visibility ) )
            throw new UnacceptableValueException( "The value $visibility is not acceptable" );

        if( $this->hasDynamicMetadataFieldDefinition( $name ) )
        {
            $d = $this->getDynamicMetadataFieldDefinition( $name );
            
            if( $visibility == self::VISIBLE || $visibility == self::INLINE || $visibility == self::HIDDEN )
            {
                $d->setVisibility( $visibility );
                return $this;
            }
            else
            {
                throw new NoSuchVisibilityException( "The definition $name does not exist" );
            }
        }
        else
        {
            throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
        }
    }
    
    public function swapDynamicMetadataFieldDefinitions( $def1, $def2 )
    {
        if( $def1 == '' || $def2 == '' )
            throw new EmptyValueException( "The value cannot be empty." );
            
        if( !in_array( $def1, $this->field_names ) )
            throw new NoSuchFieldException( "The definition $def1 does not exist" );
        
        if( !in_array( $def1, $this->field_names ) )
            throw new NoSuchFieldException( "The definition $def2 does not exist" );
            
        $first_def_pos  = -1;
        $second_def_pos = -1;
            
        $count = count( $this->dynamic_metadata_field_definitions );
    
        for( $i = 0; $i < $count; $i++ )
        {
            if( $this->dynamic_metadata_field_definitions[ $i ]->getName() == $def1 )
            {
                $first_def_pos = $i;
            }
            
            if( $this->dynamic_metadata_field_definitions[ $i ]->getName() == $def2 )
            {
                $second_def_pos = $i;
            }
        }
        
        $temp = $this->dynamic_metadata_field_definitions[ $first_def_pos ];
        $this->dynamic_metadata_field_definitions[ $first_def_pos ] = 
            $this->dynamic_metadata_field_definitions[ $second_def_pos ];
        $this->dynamic_metadata_field_definitions[ $second_def_pos ] = $temp;
        
        $this->edit();
        $this->processDynamicMetadataFieldDefinition();
        
        return $this;
    }
    
    public function swapFields( $def1, $def2 )
    {
        return $this->swapDynamicMetadataFieldDefinitions( $def1, $def2 );
    }
    
    public function swapValues( $name, $value1, $value2 )
    {
        $def = $this->getDynamicMetadataFieldDefinition( $name );
        $def->swapValues( $value1, $value2 );
        $this->edit();
    	$this->processDynamicMetadataFieldDefinition();

        return $this;
    }
    
    public function unsetSelectedByDefault( $name, $value )
    {
        $value = trim( $value );
        
        if( $value == '' )
            throw new EmptyValueException( "The value cannot be empty." );
    
        if( $this->hasDynamicMetadataFieldDefinition( $name ) )
        {
            $d = $this->getDynamicMetadataFieldDefinition( $name );
            
            if( $d->hasPossibleValue( $value ) )
            {
                $d->unsetSelectedByDefault( $value );
            }
        }
        else
        {
            throw new NoSuchMetadataFieldDefinitionException( "The definition $name does not exist" );
        }
            
        return $this;
    }
    
    private function processDynamicMetadataFieldDefinition()
    {
        $this->dynamic_metadata_field_definitions = array();
        $this->field_names                        = array();

        $definitions = 
            $this->getProperty()->dynamicMetadataFieldDefinitions->
            dynamicMetadataFieldDefinition;
            
        if( !is_array( $definitions ) )
        {
            $definitions = array( $definitions );
        }
        
        $count = count( $definitions );
        
        for( $i = 0; $i < $count; $i++ )
        {
            $this->dynamic_metadata_field_definitions[] = 
                new DynamicMetadataFieldDefinition( $definitions[ $i ] );
            $this->field_names[] = $definitions[ $i ]->name;
        }
    }

    private $dynamic_metadata_field_definitions;
    private $field_names;
}
?>
