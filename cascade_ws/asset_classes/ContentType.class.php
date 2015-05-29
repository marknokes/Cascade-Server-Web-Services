<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 9/22/2014 Added setDataDefinition, setMetadataSet, and setPageConfigurationSet.
 */
class ContentType extends ContainedAsset
{
    const DEBUG = false;
    const TYPE  = T::CONTENTTYPE;
    
    const PUBLISH_MODE_ALL_DESTINATIONS = T::ALLDESTINATIONS;
    const PUBLISH_MODE_DO_NOT_PUBLISH   = T::DONOTPUBLISH;
    
    // metadata set wired-fields
    const AUTHOR           = "author";
    const DISPLAY_NAME     = "displayName";
    const END_DATE         = "endDate";
    const KEYWORDS         = "keywords";
    const META_DESCRIPTION = "metaDescription";
    const REVIEW_DATE      = "reviewDate";
    const START_DATE       = "startDate";
    const SUMMARY          = "summary";
    const TEASER           = "teaser";
    const TITLE            = "title";
    
    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        $this->processContentTypePageConfigurations(
            $this->getProperty()->contentTypePageConfigurations->
            contentTypePageConfiguration );
        
        if( isset( $this->getProperty()->inlineEditableFields ) &&
        	isset( $this->getProperty()->inlineEditableFields->inlineEditableField ))
            $this->processInlineEditableFields(
                $this->getProperty()->inlineEditableFields->inlineEditableField );
        
        if( $this->getProperty()->dataDefinitionId != NULL )
        {
            $this->data_definition = new DataDefinition(
                $service, $service->createId( 
                    DataDefinition::TYPE, 
                    $this->getProperty()->dataDefinitionId )
            );
        }
        
        $this->metadata_set = new MetadataSet(
            $service, $service->createId( 
                MetadataSet::TYPE, 
                $this->getProperty()->metadataSetId )
        );
        
        $this->configuration_set = new PageConfigurationSet(
            $service, $service->createId( 
                PageConfigurationSet::TYPE, 
                $this->getProperty()->pageConfigurationSetId )
        );
        
        $this->wired_field_types = array( self::AUTHOR, self::DISPLAY_NAME, self::END_DATE,
            self::KEYWORDS, self::META_DESCRIPTION, self::REVIEW_DATE, self::START_DATE,
            self::SUMMARY, self::TEASER, self::TITLE );
    }
    
    public function addInlineEditableField( $config, $region, $group_path, $type, $name )
    {
        $identifier = $config . DataDefinition::DELIMITER .
            $region . DataDefinition::DELIMITER .
            ( $group_path != NULL && $group_path != 'NULL' ? 
                str_replace( '/', DataDefinition::DELIMITER, $group_path ) :
                'NULL'
            ) . DataDefinition::DELIMITER .
            $type . DataDefinition::DELIMITER . $name;
            
        if( $this->hasInlineEditableField( $identifier ) )
        {
            echo "The field already exists." . BR;
            return $this;
        }
    
        if( !$this->hasPageConfiguration( $config ) )
        {
            throw new NoSuchPageConfigurationException( "The page configuration $config does not exist." );
        }
        
        if( !$this->hasRegion( $config, $region ) )
        {
            throw new NoSuchPageRegionException( "The page region $region does not exist." );
        }
        
        if( $type == T::WIRED_METADATA && !in_array( $name, $this->wired_field_types ) )
        {
            throw new Exception( "The name $name is not acceptable." );
        }
        else if( $type == T::DYNAMIC_METADATA && !in_array( 
            $name, $this->metadata_set->getDynamicMetadataFieldDefinitionNames() ) )
        {
            throw new NoSuchFieldException( "The field $name does not exist." );
        }
        
        if( $group_path != NULL && $group_path != 'NULL' )
        {
            $group_path = str_replace( '/', DataDefinition::DELIMITER, $group_path );
            $field_name = $group_path . DataDefinition::DELIMITER . $name;
            
            if( !$this->data_definition->hasField( $field_name ) )
            {
                throw new NoSuchFieldException( "The field $name does not exist." );
            }
        }
        
        $field_std                          = new stdClass();
        $field_std->pageConfigurationName   = $config;
        $field_std->pageRegionName          = $region;
        $field_std->dataDefinitionGroupPath = ( $group_path == NULL || $group_path == 'NULL' ? NULL : $group_path );
        $field_std->type                    = $type;
        $field_std->name                    = ( $name == NULL || $name == 'NULL' ? NULL : $name );
        $field = new InlineEditableField( $field_std );
        
        $this->inline_editable_fields[] = $field;
        $this->inline_editable_field_map[ $field->getIdentifier() ] = $field;
        $this->inline_editable_field_names = array_keys( $this->inline_editable_field_map );
        
        var_dump( $this->inline_editable_fields );
        
        return $this;
    }
    
    public function display()
    {
        parent::display();
             
        foreach( $this->content_type_page_configurations as $config )
        {
            $config->display();
        }
             
        return $this;
    }
    
    public function edit()
    {
        $asset = new stdClass();
        $this->getProperty()->contentTypePageConfigurations->
            contentTypePageConfiguration = array();
        
        foreach( $this->content_type_page_configurations as $config )
        {
            $this->getProperty()->contentTypePageConfigurations->
                contentTypePageConfiguration[] = $config->toStdClass();
        }

        $editable_count = count( $this->inline_editable_fields );
        
        $this->getProperty()->inlineEditableFields = new stdClass();
        
        if( $editable_count == 1 )
        {
            $this->getProperty()->inlineEditableFields->inlineEditableField =
                $this->inline_editable_fields[0]->toStdClass();
        }
        else if( $editable_count > 1 )
        {
            $this->getProperty()->inlineEditableFields->inlineEditableField = array();
            
            foreach( $this->inline_editable_fields as $field )
            {
                $this->getProperty()->inlineEditableFields->inlineEditableField[] =
                    $field->toStdClass();
            }
        }
        
        $asset->{ $p = $this->getPropertyName() } = $this->getProperty();
        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
            throw new EditingFailureException( 
                M::EDIT_ASSET_FAILURE . $service->getMessage() );
        }
        return $this->reloadProperty();
    }
    
    public function getConfigurationSet()
    {
        return $this->getPageConfigurationSet();
    }
    
    public function getContentTypePageConfigurationNames()
    {
        return $this->content_type_page_configuration_names;
    }
    
    public function getDataDefinition()
    {
        if( $this->getProperty()->dataDefinitionId != NULL )
        {
            $service = $this->getService();
        
            return Asset::getAsset( $service,
                DataDefinition::TYPE,
                $this->getProperty()->dataDefinitionId );
        }
        return NULL;
    }
    
    public function getDataDefinitionId()
    {
        return $this->getProperty()->dataDefinitionId;
    }
    
    public function getDataDefinitionPath()
    {
        return $this->getProperty()->dataDefinitionPath;
    }
    
    public function getInlineEditableFieldNames()
    {
        return $this->inline_editable_field_names;
    }
    
    public function getInlineEditableFields()
    {
        return $this->getProperty()->inlineEditableFields;
    }
    
    public function getMetadataSet()
    {
        $service = $this->getService();
        
        return Asset::getAsset( $service,
            MetadataSet::TYPE,
            $this->getProperty()->metadataSetId );
    }
    
    public function getMetadataSetId()
    {
        return $this->getProperty()->metadataSetId;
    }
    
    public function getMetadataSetPath()
    {
        return $this->getProperty()->metadataSetPath;
    }
    
    public function getPageConfigurationSet()
    {
        $service = $this->getService();
        
        return Asset::getAsset( $service,
            PageConfigurationSet::TYPE,
            $this->getProperty()->pageConfigurationSetId );
    }
        
    public function getPageConfigurationSetId()
    {
        return $this->getProperty()->pageConfigurationSetId;
    }
    
    public function getPageConfigurationSetPath()
    {
        return $this->getProperty()->pageConfigurationSetPath;
    }
    
    public function getPublishMode( $config_name )
    {
        if( !in_array( $config_name, $this->content_type_page_configuration_names ) )
        {
            throw new Exception( "The page configuration $config_name does not exist." );
        }
    
        foreach( $this->content_type_page_configurations as $config )
        {
            if( $config->getPageConfigurationName() == $config_name )
            {
                return $config->getPublishMode();
            }
        }
    }
    
    public function hasDataDefinitionGroupPath( $name )
    {
        $name = str_replace( '/', DataDefinition::DELIMITER, $name );
        return in_array( $name, $this->data_definition->getIdentifiers() );
    }
    
    public function hasInlineEditableField( $name )
    {
        return in_array( $name, $this->inline_editable_field_names );
    }
    
    public function hasPageConfiguration( $name )
    {
        return in_array( $name, $this->content_type_page_configuration_names );
    }
    
    public function hasRegion( $config_name, $region_name )
    {
        return in_array( $region_name, 
            $this->configuration_set->getPageRegionNames( $config_name ) );
    }
    
    public function removeInlineEditableField( $identifier )
    {
        if( !$this->hasInlineEditableField( $identifier ) )
        {
            throw new NoSuchFieldException( "The field $identifier does not exist." );
        }
        
        $count = count( $this->inline_editable_fields );
        
        for( $i = 0; $i < $count; $i++ )
        {
            if( $this->inline_editable_fields[ $i ]->getIdentifier() == $identifier )
            {
                $field_before = array_splice( $this->inline_editable_fields, 0, $i );
                
                $field_after = array();
                
                if( $count > $i + 1 )
                {
                    $field_after  = array_splice( $this->inline_editable_fields, $i + 1 );
                }
                $this->inline_editable_fields = array_merge( $field_before, $field_after );
                break;
            }
        }
        
        unset( $this->inline_editable_field_map[ $identifier ] );
        $this->inline_editable_field_names = array_keys( $this->inline_editable_field_map );
        
        return $this;
    }

    public function setDataDefinition( DataDefinition $dd=NULL )
    {
		if( $dd != NULL )
        {
            $this->getProperty()->dataDefinitionId   = $dd->getId();
            $this->getProperty()->dataDefinitionPath = $dd->getPath();
        }
        else
        {
            $this->getProperty()->dataDefinitionId   = NULL;
            $this->getProperty()->dataDefinitionPath = NULL;
        }
        return $this;
    }
    
    public function setMetadataSet( MetadataSet $ms )
    {
        $this->getProperty()->metadataSetId   = $ms->getId();
        $this->getProperty()->metadataSetPath = $ms->getPath();
        return $this;
    }
    
    public function setPageConfigurationSet( PageConfigurationSet $pcs )
    {
        $this->getProperty()->pageConfigurationSetId   = $pcs->getId();
        $this->getProperty()->pageConfigurationSetPath = $pcs->getPath();
        return $this;
    }
    
    public function setPublishMode( $config_name, $mode )
    {
        if( !in_array( $config_name, $this->content_type_page_configuration_names ) )
        {
            throw new Exception( "The page configuration $config_name does not exist." );
        }
    
        if( $mode != self::PUBLISH_MODE_ALL_DESTINATIONS && 
            $mode != self::PUBLISH_MODE_DO_NOT_PUBLISH )
        {
            throw new Exception( "The mode $mode is not supported." );
        }
        
        foreach( $this->content_type_page_configurations as $config )
        {
            if( $config->getPageConfigurationName() == $config_name )
            {
                $config->setPublishMode( $mode );
            }
        }
        
        return $this;
    }
    
    private function processContentTypePageConfigurations( $configs )
    {
        $this->content_type_page_configurations = array();
        
        // store the names of page configs
        $this->content_type_page_configuration_names = array();

        if( !is_array( $configs ) )
        {
            $configs = array( $configs );
        }
        
        foreach( $configs as $config )
        {
            $this->content_type_page_configurations[] = 
                new ContentTypePageConfiguration( $config );
                
            $this->content_type_page_configuration_names[] = 
                $config->pageConfigurationName;    
        }
    }
    
    private function processInlineEditableFields( $fields )
    {
        $this->inline_editable_fields      = array();
        $this->inline_editable_field_map   = array();
        $this->inline_editable_field_names = array();

        if( $fields != NULL )
        {
            if( !is_array( $fields ) )
            {
                $fields = array( $fields );
            }
            
            foreach( $fields as $field )
            {
                $ief = new InlineEditableField( $field );
                $this->inline_editable_fields[] = $ief;
                //echo $ief->getIdentifier() . BR;
                $this->inline_editable_field_map[ $ief->getIdentifier() ] = $ief;
            }
            
            $this->inline_editable_field_names = 
                array_keys( $this->inline_editable_field_map );
        }
    }
    
    private $content_type_page_configurations;
    private $content_type_page_configuration_names;
    private $inline_editable_fields;
    private $inline_editable_field_map;
    private $inline_editable_field_names;
    
    private $data_definition;
    private $metadata_set;
    private $configuration_set;
    private $wired_field_types;
}