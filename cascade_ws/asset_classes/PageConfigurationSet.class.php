<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 9/22/2014 Fixed a bug in addPageConfiguration.
  * 9/8/2014 Fixed a bug in deletePageConfiguration.
  * 7/3/2014 Added addConfiguration.
 */
class PageConfigurationSet extends ContainedAsset
{
    const DEBUG = false;
    const TYPE  = T::CONFIGURATIONSET;
    
    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        $this->processPageConfigurations();
    }
    
    public function addConfiguration( $name, Template $t, $extension, $type )
    {
    	return $this->addPageConfiguration( $name, $t, $extension, $type );
    }
    
    public function addPageConfiguration( $name, Template $t, $extension, $type )
    {
    	if( trim( $extension ) == "" )
    		throw new EmptyValueException( M::EMPTY_FILE_EXTENSION );
    		
    	if( !SerializationTypeValues::isSerializationTypeValue( $type ) )
    		throw new WrongSerializationTypeException( "The serialization type $type is not acceptable. " );
    	
		$config                    = AssetTemplate::getPageConfiguration();
		$config->name              = $name;
		$config->templateId        = $t->getId();
		$config->templatePath      = $t->getPath();
		$config->pageRegions       = $t->getPageRegionStdForPageConfiguration();
		$config->outputExtension   = $extension;
		$config->serializationType = $type;

        $p = new PageConfiguration( $config, $this->getService() );
        $this->page_configurations[] = $p;
        $this->edit();
            
        $this->processPageConfigurations( 
            $this->getProperty()->pageConfigurations->pageConfiguration );
    	return $this;
    }
    
    public function deleteConfiguration( $name )
    {
        return $this->deletePageConfiguration( $name );        
    }
    
    public function deletePageConfiguration( $name )
    {
        if( $this->getDefaultConfiguration() == $name )
        {
            throw new Exception( "Cannot delete the default configuration." );
        }
        
        if( !$this->hasConfiguration( $name ) )
        	return $this;
        	
        $id = $this->page_configuration_map[ $name ]->getId();
        $service = $this->getService();
        $service->delete( $service->createId( T::CONFIGURATION, $id ) );
        
        $this->reloadProperty();
            
        $this->processPageConfigurations( 
            $this->getProperty()->pageConfigurations->pageConfiguration );

        return $this;        
    }
    
    public function edit()
    {
        $asset        = new stdClass();
        $config_array = array();
        $config_count = count( $this->page_configurations );
        
        // convert PageConfiguration objects back to stdClass objects
        for( $i = 0; $i < $config_count; $i++ )
        {
            $config_array[ $i ] = $this->page_configurations[ $i ]->toStdClass();
        }
        
        $this->getProperty()->pageConfigurations->pageConfiguration = $config_array;
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
    
    public function getConfiguration( $name )
    {
        return $this->getPageConfiguration( $name );
    }

    
    public function getDefaultConfiguration()
    {
        foreach( $this->page_configurations as $page_configuration )
        {
            if( $page_configuration->getDefaultConfiguration() )
            {
                return $page_configuration;
            }
        }
    }
    
    public function getIncludeXMLDeclaration( $config )
    {
        return $this->page_configuration_map[ $config ]->getIncludeXMLDeclaration();
    }
    
    public function getOutputExtension( $config )
    {
        return $this->page_configuration_map[ $config ]->getOutputExtension();
    }
    
    public function getPageConfiguration( $name )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
            throw new NoSuchPageConfigurationException( "The page configuration $name does not exists." );
            
        $count = $this->page_configurations;
        
        for( $i = 0; $i < $count; $i++ )
        {
            if( $this->page_configurations[ $i ]->getName() == $name )
                return $this->page_configurations[ $i ];
        }
    }
    
    public function getPageConfigurationNames()
    {
        return $this->page_configuration_names;
    }
    
    public function getPageConfigurations()
    {
        $config_array = array();
        $config_count = count( $this->page_configurations );
        
        for( $i = 0; $i < $config_count; $i++ )
        {
            $config_array[ $i ] = $this->page_configurations[ $i ];
        }
        
        return $config_array;
    }
    
    public function getPageConfigurationTemplate( $name )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
            throw new NoSuchPageConfigurationException( "The page configuration $name does not exists." );
        
        $count = $this->page_configurations;

        for( $i = 0; $i < $count; $i++ )
        {
            if( $this->page_configurations[ $i ]->getName() == $name )
            {
                $id = $this->page_configurations[ $i ]->getTemplateId();
                return Asset::getAsset( $this->getService(), Template::TYPE, $id );
            }
        }
    }
    
    public function getPageRegionNames( $name )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageRegionException( 
                "The page region $region_name does not exist." );
        }
        return $this->page_configuration_map[ $name ]->getPageRegionNames();
    }
    
    public function getPageRegion( $config_name, $region_name )
    {
        if( !in_array( $config_name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageRegionException( 
                "The page region $region_name does not exist." );
        }
        return $this->page_configuration_map[ $config_name ]->getPageRegion( $region_name );
    }
    
    public function getPublishable( $config_name )
    {
        return $this->page_configuration_map[ $config_name ]->getPublishable();
    }
    
    public function getSerializationType( $config_name )
    {
        return $this->page_configuration_map[ $config_name ]->getSerializationType();
    }
    
    public function hasConfiguration( $name )
    {
        return $this->hasPageConfiguration( $name );
    }

    public function hasPageConfiguration( $name )
    {
        return in_array( $name, $this->page_configuration_names );
    }
    
    public function hasPageRegion( $config_name, $region_name )
    {
        if( !in_array( $config_name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
        return $this->page_configuration_map[ $config_name ]->hasPageRegion( $region_name );
    }
    
    public function setConfigurationPageRegionBlock( $config_name, $region_name, $block )
    {
        if( !in_array( $config_name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
        $config = $this->page_configuration_map[ $config_name ];
        $config->setPageRegionBlock( $region_name, $block );
        return $this;
    }
    
    public function setConfigurationPageRegionFormat( $config_name, $region_name, $format )
    {
        if( !in_array( $config_name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
        
        $this->page_configuration_map[ $config_name ]->setPageRegionFormat( $region_name, $format );
        return $this;
    }
    
    public function setDefaultConfiguration( $name )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
        
        foreach( $this->page_configurations as $page_configuration )
        {
            if( $page_configuration->getName() != $name )
            {
                $page_configuration->setDefaultConfiguration( false );
            }
            else
            {
                $page_configuration->setDefaultConfiguration( true );
            }
        }
        return $this;
    }
    
    public function setFormat( $name, Format $format )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
    
        $this->page_configuration_map[ $name ]->setFormat( $format );
        return $this;
    }
    
    public function setIncludeXMLDeclaration( $name, $i )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
    
        $this->page_configuration_map[ $name ]->setIncludeXMLDeclaration( $i );
        return $this;
    }
    
    public function setOutputExtension( $name, $ext )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
    
        $this->page_configuration_map[ $name ]->setOutputExtension( $ext );
        return $this;
    }
    
    public function setPublishable( $name, $p )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
    
        $this->page_configuration_map[ $name ]->setPublishable( $p );
        return $this;
    }
    
    public function setSerializationType( $name, $type )
    {
        if( !in_array( $name, $this->page_configuration_names ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $name does not exist." );
        }
    
        $this->page_configuration_map[ $name ]->setSerializationType( $type );
        return $this;
    }
    
    private function processPageConfigurations()
    {
        $this->page_configurations      = array();
        $this->page_configuration_names = array();
        $this->page_configuration_map   = array();

        $array = $this->getProperty()->pageConfigurations->pageConfiguration;
        
        if( $array != NULL )
        {
            // stdClass object
            if( !is_array( $array ) )
            {
                $array = array( $array );
            }
        }
        
        $service = $this->getService();
        
        foreach( $array as $page_configuration )
        {
            $p = new PageConfiguration( $page_configuration, $this->getService() );
            $this->page_configurations[] = $p;
            $this->page_configuration_names[] = $page_configuration->name;
            $this->page_configuration_map[ $page_configuration->name ] = $p;
        }
    }

    private $page_configurations;
    private $page_configuration_names;
    private $page_configuration_map;
}
?>