<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 9/21/2014 Fixed a bug in toStdClass.
  * 7/30/2014 Fixed bugs in setEndDate, setReviewDate, setStartDate.
  * 7/28/2014 Added isWiredField and getWiredFieldMethodName.
 */
class Metadata extends Property
{
    const DEBUG = false;
    const DUMP  = false;

    public function __construct( 
    	stdClass $obj=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$metadata_set_id=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
    	if( isset( $obj ) )
    	{
			$this->author              = $obj->author;
			$this->display_name        = $obj->displayName;
			$this->end_date            = $obj->endDate;
			$this->keywords            = $obj->keywords;
			$this->meta_description    = $obj->metaDescription;
			$this->review_date         = $obj->reviewDate;
			$this->start_date          = $obj->startDate;
			$this->summary             = $obj->summary;
			$this->teaser              = $obj->teaser;
			$this->title               = $obj->title;
			$this->service             = $service;
			$this->metadata_set        = NULL;
			$this->metadata_set_id     = $metadata_set_id;
		
			if( $obj->dynamicFields != NULL ) // could be NULL
			{
				$this->processDynamicFields( $obj->dynamicFields->dynamicField );
			}
		}
    }
    
    public function getAuthor()
    {
        return $this->author;
    }
    
    public function getDisplayName()
    {
        return $this->display_name;
    }
    
    public function getDynamicField( $name )
    {
        $name = trim( $name );
        
        if( $name == '' )
            throw new EmptyNameException( "The name cannot be empty." );
    
        foreach( $this->dynamic_fields as $field )
        {
            if( $field->getName() == $name )
                return $field;
        }
        
        throw new NoSuchFieldException( "The dynamic field $name does not exist" );
    }

    public function getDynamicFieldNames()
    {
        return $this->dynamic_field_names;
    }
    
    public function getDynamicFieldPossibleValues( $name )
    {
        return $this->getMetadataSet()->getDynamicMetadataFieldPossibleValueStrings( $name );
    }
    
    public function getDynamicFields()
    {
        return $this->dynamic_fields;
    }
    
    public function getDynamicFieldValues( $name )
    {
        $name = trim( $name );
        
        if( $name == '' )
            throw new EmptyNameException( "The name cannot be empty." );
    
        $field = $this->getDynamicField( $name );
        
        return $field->getFieldValue()->getValues();
    }
    
    public function getEndDate()
    {
        return $this->end_date;
    }
    
    public function getKeywords()
    {
        return $this->keywords;
    }
    
    public function getMetadataSet()
    {
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }

        return $this->metadata_set;
    }
    
    public function getMetaDescription()
    {
        return $this->meta_description;
    }
    
    public function getReviewDate()
    {
        return $this->review_date;
    }
    
    public function getStartDate()
    {
        return $this->start_date;
    }
    
    public function getSummary()
    {
        return $this->summary;
    }
    
    public function getTeaser()
    {
        return $this->teaser;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function hasDynamicField( $name )
    {
        if( $name == '' )
            throw new EmptyNameException( "The name cannot be empty." );
    
        return in_array( $name, $this->dynamic_field_names );
    }
    
    public function setAuthor( $author )
    {
        $author = trim( $author );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getAuthorFieldRequired() && $author == '' )
        {
            throw new RequiredFieldException( "The author field is required." );
        }

        $this->author = $author;
        return $this;
    }
    
    public function setDisplayName( $display_name )
    {
        $display_name = trim( $display_name );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getDisplayNameFieldRequired() && $display_name == '' )
        {
            throw new RequiredFieldException( "The displayName field is required." );
        }

        $this->display_name = $display_name;
        return $this;
    }
    
    public function setDynamicField( $field, $values )
    {
        return $this->setDynamicFieldValue( $field, $values );
    }
    
    public function setDynamicFieldValue( $field, $values ) // string or string array
    {
        if( !is_array( $values ) )
        {
            $values = array( $values );
        }
        
        $v_count = count( $values );
        
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = Asset::getAsset( 
                $this->service, MetadataSet::TYPE, $this->metadata_set_id );
        }
        
        $df_def     = $this->metadata_set->getDynamicMetadataFieldDefinition( $field );
        $field_type = $df_def->getFieldType();
        $required   = $df_def->getRequired();
        $df         = $this->getDynamicField( $field );
        
        // text can accept anything
        if( $field_type == T::TEXT && $v_count == 1 )
        {
            $value = $values[0];
            
            if( $value == NULL ) // turn NULL to empty string
                $value = '';
            
            if( $required && $value == '' )
            {
                throw new RequiredFieldException( "The $field_type requires non-empty value" );
            }
            
            $v = new stdClass();
            $v->value = $value;
            $df->setValue( array( $v ) );
        }
        // radio and dropdown can accept only one value
        else if( ( $field_type == T::RADIO || $field_type == T::DROPDOWN ) &&
            $v_count == 1 )
        {
            $value = $values[0]; // read first value
            
            if( $value == '' ) // turn empty string to NULL
                $value = NULL;
            
            if( $required && $value == NULL ) // cannot be empty if required
                throw new RequiredFieldException( "The $field_type requires non-empty value" );
            
            $possible_values = $df_def->getPossibleValueStrings(); // read from metadataSet
            
            if( !in_array( $value, $possible_values ) && $value != NULL ) // undefined value
                throw new NoSuchValueException( "The value $value does not exist" );
            
            $v = new stdClass();
            
            if( $value != '' )
                $v->value = $value;
        
            $df->setValue( array( $v ) );
        }
        else if( ( $field_type == T::CHECKBOX || $field_type == T::MULTISELECT ) &&
            $v_count > 0 )
        {
			if( self::DEBUG ){ DebugUtility::out( 'Setting values for checkbox or multiselect' ); }

            if( $required && ( in_array( NULL, $values) || in_array( '', $values ) ) )
            {
                throw new RequiredFieldException( "The $field_type requires non-empty value" );
            }
        
            $possible_values = $df_def->getPossibleValueStrings();
            
            foreach( $values as $value )
            {
				if( self::DEBUG ){ DebugUtility::out( "Value: $value" ); }

                if( !in_array( $value, $possible_values ) && $value != NULL )
                {
                    throw new NoSuchValueException( "The value $value does not exist" );
                }
            }
            
            $v_array = array();
            
            foreach( $values as $value )
            {
                $v = new stdClass();
                $v->value = $value;
                $v_array[] = $v;
            }
            
            $df->setValue( $v_array );
			if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $df->toStdClass() ); }

        }
        
		//if( self::DEBUG && self::DUMP ){ DebugUtility::dump( $this ); }

        return $this;
    }
    
    public function setDynamicFieldValues( $field, $values )
    {
        return $this->setDynamicField( $field, $values );
    }
    
    public function setEndDate( $end_date )
    {
        $end_date = trim( $end_date );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getEndDateFieldRequired() && ( $end_date == '' || $end_date == NULL ) )
        {
            throw new RequiredFieldException( "The endDate field is required." );
        }

		if( $end_date == "" )
			$end_date = NULL;
			
        $this->end_date = $end_date;
        return $this;
    }
    
    public function setKeywords( $keywords )
    {
        $keywords = trim( $keywords );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getKeywordsFieldRequired() && $keywords == '' )
        {
            throw new RequiredFieldException( "The keywords field is required." );
        }

        $this->keywords = $keywords;
        return $this;
    }
    
    public function setMetaDescription( $meta_description )
    {
        $meta_description = trim( $meta_description );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getDescriptionFieldRequired() && $meta_description == '' )
        {
            throw new RequiredFieldException( "The metaDescription field is required." );
        }

        $this->meta_description = $meta_description;
        return $this;
    }
    
    public function setReviewDate( $review_date )
    {
        $review_date = trim( $review_date );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getReviewDateFieldRequired() && ( $review_date == '' || $review_date == NULL ) )
        {
            throw new RequiredFieldException( "The reviewDate field is required." );
        }

		if( $review_date == "" )
			$review_date = NULL;
			
        $this->review_date = $review_date;
        return $this;
    }
    
    public function setStartDate( $start_date )
    {
        $start_date = trim( $start_date );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getStartDateFieldRequired() && ( $start_date == '' || $start_date == NULL ) )
        {
            throw new RequiredFieldException( "The startDate field is required." );
        }
        
        if( $start_date == "" )
        	$start_date = NULL;

        $this->start_date = $start_date;
        return $this;
    }
    
    public function setSummary( $summary )
    {
        $summary = trim( $summary );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getSummaryFieldRequired() && $summary == '' )
        {
            throw new RequiredFieldException( "The summary field is required." );
        }

        $this->summary = $summary;
        return $this;
    }
    
    public function setTeaser( $teaser )
    {
        $teaser = trim( $teaser );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getTeaserFieldRequired() && $teaser == '' )
        {
            throw new RequiredFieldException( "The teaser field is required." );
        }

        $this->teaser = $teaser;
        return $this;
    }
    
    public function setTitle( $title )
    {
        $title = trim( $title );
    
        if( $this->metadata_set == NULL )
        {
            $this->metadata_set = new MetadataSet( 
                $this->service, $this->service->createId( 
                    MetadataSet::TYPE, $this->metadata_set_id ) );
        }
                
        if( $this->metadata_set->getTitleFieldRequired() && $title == '' )
        {
            throw new RequiredFieldException( "The title field is required." );
        }

        $this->title = $title;
        return $this;
    }
    
    public function toStdClass()
    {
        $obj                  = new stdClass();
        $obj->author          = $this->author;
        $obj->displayName     = $this->display_name;
        $obj->endDate         = $this->end_date;
        $obj->keywords        = $this->keywords;
        $obj->metaDescription = $this->meta_description;
        $obj->reviewDate      = $this->review_date;
        $obj->startDate       = $this->start_date;
        $obj->summary         = $this->summary;
        $obj->teaser          = $this->teaser;
        $obj->title           = $this->title;

        $count = count( $this->dynamic_fields );
        
        if( $count == 0 )
        {
            $obj->dynamicFields = NULL;
        }
        else if( $count == 1 )
        {
        	$obj->dynamicFields = new stdClass();
            $obj->dynamicFields->dynamicField = $this->dynamic_fields[0]->toStdClass();
        }
        else
        {
        	$obj->dynamicFields = new stdClass();
            $obj->dynamicFields->dynamicField = array();
            
            for( $i = 0; $i < $count; $i++ )
            {
                $obj->dynamicFields->dynamicField[] = 
                    $this->dynamic_fields[$i]->toStdClass();
            }
        }
        
        return $obj;
    }
    
    public static function getWiredFieldMethodName( $field_name )
    {
    	if( self::isWiredField( $field_name ) )
    	{
    		return StringUtility::getMethodName( $field_name );
    	}
    	return NULL;
    }

    public static function isWiredField( $field_name )
    {
    	return in_array( $field_name, self::$wired_fields );
    }
    
    private function processDynamicFields( $fields )
    {
        $this->dynamic_fields      = array();
        $this->dynamic_field_names = array();

        if( !is_array( $fields ) )
        {
            $fields = array( $fields );
        }
        
        foreach( $fields as $field )
        {
            $df = new DynamicField( $field );
            $this->dynamic_fields[] = $df;
            $this->dynamic_field_names[] = $field->name;
        }
    }
    
    private static $wired_fields = array(
    	'author', 'displayName', 'endDate', 'keywords', 'metaDescription',
    	'reviewDate', 'startDate', 'summary', 'teaser', 'title'
    );
    
    private $author;
    private $display_name;
    private $end_date;
    private $keywords;
    private $meta_description;
    private $review_date;
    private $start_date;
    private $summary;
    private $teaser;
    private $title;
    private $dynamic_fields;
    private $dynamic_field_names;
    private $service;
    private $metadata_set_id;
}
?>
