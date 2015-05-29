<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/1/2015 Changed signature of edit and added editWithoutException.
  *   Reason: when changing the content type associated with a page,
  *           if a different data definition is used, phantom nodes will
  *           cause a lot of exceptions. The restriction must be loosened
  *           so that a page can be modified.
  * 4/9/2015 Added a flag to setContentType to avoid exception.
  * 2/24/2015 Added getPossibleValues.
  * 2/23/2015 Added the missing isMultiLineNode.
  * 10/2/2014 Fixed a bug in edit.
  * 9/18/2014 Added getMetadataSet, getMetadataSetId, getMetadataSetPath.
  * 8/29/2014 Fixed bugs in appendSibling and removeLastSibling.
  * 8/27/2014 Added getParentFolder, getParentFolderId, getParentFolderPath.
  * 8/20/2014 Added hasConfiguration.
  * 7/23/2014 Split getPageLevelRegionBlockFormat into getPageLevelRegionBlockFormat and getBlockFormatMap and
  * added no-block and no-format.
  * 7/22/2014 Added getMetadataStdClass, isPublishable, setMetadata.
  * 6/5/2014 Fixed a bug in getPageLevelRegionBlockFormat.
  * 5/13/2014 Added createNInstancesForMultipleField 
  *   and replaced all string literals with constants
 */
class Page extends Linkable
{
    const DEBUG = false;
    const DUMP  = false;
    const TYPE  = T::PAGE;

    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        $this->content_type = new ContentType( 
            $service, $service->createId( ContentType::TYPE, 
            $this->getProperty()->contentTypeId ) );
            
        parent::setPageContentType( $this->content_type );
            
        if( $this->getProperty()->structuredData != NULL )
        {
            $this->data_definition_id = $this->content_type->getDataDefinitionId();

            // structuredDataNode could be empty for xml pages
            if( isset( $this->getProperty()->structuredData ) &&
            	isset( $this->getProperty()->structuredData->structuredDataNodes ) &&
            	isset( $this->getProperty()->structuredData->structuredDataNodes->structuredDataNode )
            )
            {
                $this->processStructuredData( $this->data_definition_id );
            }
        }
        else
        {
            $this->xhtml = $this->getProperty()->xhtml;
        }
        
        $this->processPageConfigurations( $this->getProperty()->pageConfigurations->pageConfiguration );
    }

    public function appendSibling( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }

        $this->structured_data->appendSibling( $identifier );
        $this->edit();
        return $this;
    }

    public function createNInstancesForMultipleField( $number, $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
      
        $number = intval( $number );
        
        if( !$number > 0 )
        {
            throw new UnacceptableValueException( "The value $number is not a number." );
        }
        
        if( !$this->hasNode( $identifier ) )
        {
            throw new NodeException( "The node $identifier does not exist." );
        }
        
        $num_of_instances  = $this->getNumberOfSiblings( $identifier );
    
        if( $num_of_instances < $number ) // more needed
        {
            while( $this->getNumberOfSiblings( $identifier ) != $number )
            {
                $this->appendSibling( $identifier );
            }
        }
        else if( $num_of_instances > $number )
        {
            while( $this->getNumberOfSiblings( $identifier ) != $number )
            {
                $this->removeLastSibling( $identifier );
            }
        }

        $this->reloadProperty();
        $this->processStructuredData( $this->data_definition_id );
        return $this;
    }
    
    public function displayDataDefinition()
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->getDataDefinition()->displayXML();
        return $this;
    }
    
    public function displayXhtml()
    {
        if( !$this->hasStructuredData() )
        {
            $xhtml_string = XMLUtility::replaceBrackets( $this->xhtml );
            echo S_H2 . 'XHTML' . E_H2;
            echo $xhtml_string . HR;
        }
        return $this;
    }
    
    public function edit( 
        Workflow $wf=NULL, 
        WorkflowDefinition $wd=NULL, 
        $new_workflow_name="", 
        $comment="",
        $exception=true)
    {
        $asset = new stdClass();
        $page  = $this->getProperty();
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $page->pageConfigurations ); }

        $page->metadata = $this->getMetadata()->toStdClass();
        
        if( $this->structured_data != NULL )
        {
            $page->structuredData = $this->structured_data->toStdClass();
            $page->xhtml = NULL;
        }
        else
        {
            $page->structuredData = NULL;
            $page->xhtml = $this->xhtml;
        }
        
        $page->pageConfigurations->pageConfiguration = array();
        
        foreach( $this->page_configurations as $config )
        {
            $page->pageConfigurations->pageConfiguration[] = $config->toStdClass();
        }
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $page->pageConfigurations ); }
        
        if( $wf != NULL )
        {
            if( trim( $comment ) == "" )
                throw new EmptyValueException( M::EMPTY_COMMENT );
                
            $wf_config                       = new stdClass();
            $wf_config->workflowName         = $wf->getName();
            $wf_config->workflowDefinitionId = $wf->getId();
            $wf_config->workflowComments     = $comment;
            $asset->workflowConfiguration    = $wf_config;
        }
        else if( $wd != NULL )
        {
            if( trim( $comment ) == "" )
                throw new EmptyValueException( M::EMPTY_COMMENT );
                
            if( trim( $new_workflow_name ) == "" )
                throw new EmptyValueException( M::EMPTY_WORKFLOW_NAME );
                
            $wf_config                       = new stdClass();
            $wf_config->workflowName         = $new_workflow_name;
            $wf_config->workflowDefinitionId = $wd->getId();
            $wf_config->workflowComments     = $comment;
            $asset->workflowConfiguration    = $wf_config;
        }
        
        $asset->{ $p = $this->getPropertyName() } = $page;
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $page ); }
        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
            throw new EditingFailureException( 
                M::EDIT_ASSET_FAILURE . $service->getMessage() );
        }
        
        if( $exception )
        	$this->reloadProperty();
        
        if( isset( $this->data_definition_id ) && $exception )
        	$this->processStructuredData( $this->data_definition_id );
        return $this;
    }
    
    public function getAssetNodeType( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
        }
        
        return $this->structured_data->getAssetNodeType( $identifier );
    }
    
    public function getBlockFormatMap( PageConfiguration $configuration )
    {
        $block_format_array  = array();
        $configuration_name  = $configuration->getName();
        $config_page_regions = $configuration->getPageRegions();
        $config_region_names = $configuration->getPageRegionNames();
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $config_region_names ); }
        
        $page_level_config  = $this->page_configuration_map[ $configuration_name ];
        $page_level_regions = $page_level_config->getPageRegions();
        $page_region_names  = $page_level_config->getPageRegionNames();
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $page_region_names ); }
        
        $template = $this->getContentType()->getConfigurationSet()->
            getPageConfigurationTemplate( $configuration_name );
        $template_region_names = $template->getPageRegionNames();
        
        foreach( $page_region_names as $page_region_name )
        {
            // initialize id variables
            $block_id = NULL;
            $format_id = NULL;

            // for debugging
            if( self::DEBUG )
            {
                DebugUtility::out( $page_region_name );

                if( $template->hasPageRegion( $page_region_name ) )
                {
                    DebugUtility::out( "template block: " . 
                        $template->getPageRegion( $page_region_name )->getBlockId() );
                    DebugUtility::out( "template format: " . 
                        $template->getPageRegion( $page_region_name )->getFormatId() );
                }
            
                if( $configuration->hasPageRegion( $page_region_name ) )
                {
                    DebugUtility::out( "Config block: " . 
                        $configuration->getPageRegion( $page_region_name )->getBlockId() );
                    DebugUtility::out( "Config format: " . 
                        $configuration->getPageRegion( $page_region_name )->getFormatId() );
                }
                
                if( $page_level_config->hasPageRegion( $page_region_name ) )
                {
                    DebugUtility::out( "Page block: " . 
                        $page_level_config->getPageRegion( $page_region_name )->getBlockId() );
                    DebugUtility::out( "Page format: " . 
                        $page_level_config->getPageRegion( $page_region_name )->getFormatId() );
                } 
            }
            
            // template level
            if( $template->hasPageRegion( $page_region_name ) )
            {
                $template_block_id  = $template->
                    getPageRegion( $page_region_name )->getBlockId();
                $template_format_id = $template->
                    getPageRegion( $page_region_name )->getFormatId();
            }
            // config level
            if( $configuration->hasPageRegion( $page_region_name ) )
            {
                $config_block_id  = $configuration->
                    getPageRegion( $page_region_name )->getBlockId();
                $config_format_id = $configuration->
                    getPageRegion( $page_region_name )->getFormatId();
            }
            // page level
            else
            {
                $config_block_id  = NULL;
                $config_format_id = NULL;
            }
            
            if( $page_level_config->hasPageRegion( $page_region_name ) )
            {
                $page_block_id  = $page_level_config->
                    getPageRegion( $page_region_name )->getBlockId();
                $page_format_id = $page_level_config->
                    getPageRegion( $page_region_name )->getFormatId();
                $page_no_block  = $page_level_config->
                    getPageRegion( $page_region_name )->getNoBlock();
                $page_no_format = $page_level_config->
                    getPageRegion( $page_region_name )->getNoFormat();
            } 

            if( isset( $page_block_id ) )
            {
                $block_id = NULL;
                
                if( !isset( $config_block_id ) )
                {
                    if( $page_block_id != $template_block_id )
                    {
                        $block_id = $page_block_id;
                    }
                }
                else if( $config_block_id != $page_block_id )
                {
                    $block_id = $page_block_id;
                }
            }

            if( isset( $page_format_id ) )
            {
                $format_id = NULL;
                
                if( !isset( $config_format_id ) )
                {
                    if( $page_format_id != $template_format_id )
                    {
                        $format_id = $page_format_id;
                    }
                }
                else if( $config_format_id != $page_format_id )
                {
                    $format_id = $page_format_id;
                }
            }
            // store page-level block/format info
            if( $block_id != NULL )
            {
                if( !isset( $block_format_array[ $page_region_name ] ) )
                {
                    $block_format_array[ $page_region_name ] = array();
                }
                
                $block_format_array[ $page_region_name ][ 'block' ] = $block_id;
            }
            
            if( $format_id != NULL )
            {
                if( !isset( $block_format_array[ $page_region_name ] ) )
                {
                    $block_format_array[ $page_region_name ] = array();
                }
                
                $block_format_array[ $page_region_name ][ 'format' ] = $format_id;
            }
            
            if( $page_no_block )
            {
                $block_format_array[ $page_region_name ][ 'no-block' ] = true;
            }

            if( $page_no_format )
            {
                $block_format_array[ $page_region_name ]['no-format' ] = true;
            }
        }
        return $block_format_array;
    }
    
    public function getBlockId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
        }
        
        return $this->structured_data->getBlockId( $identifier );
    }
    
    public function getBlockPath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
        }
        
        return $this->structured_data->getBlockPath( $identifier );
    }
    
    public function getConfigurationSet()
    {
        return $this->getPageConfigurationSet();
    }
    
    public function getConfigurationSetId()
    {
        return $this->getProperty()->configurationSetId; // NULL for page
    }
    
    public function getConfigurationSetPath()
    {
        return $this->getProperty()->configurationSetPath; // NULL for page
    }
    
    public function getContentType()
    {
        $service = $this->getService();
        
        return Asset::getAsset( $service,
            ContentType::TYPE,
            $this->getProperty()->contentTypeId );
    }

    public function getContentTypeId()
    {
        return $this->getProperty()->contentTypeId;
    }
    
    public function getContentTypePath()
    {
        return $this->getProperty()->contentTypePath;
    }
    
    public function getDataDefinition()
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getDataDefinition();
    }
    
    public function getFileId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getFileId( $identifier );
    }
    
    public function getFilePath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getFilePath( $identifier );
    }
    
    public function getIdentifiers()
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getIdentifiers();
    }
    
    public function getLastPublishedDate()
    {
        return $this->getProperty()->lastPublishedDate;
    }
    
    public function getLastPublishedBy()
    {
        return $this->getProperty()->lastPublishedBy;
    }
    
    public function getLinkableId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getLinkableId( $identifier );
    }
    
    public function getLinkablePath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getLinkablePath( $identifier );
    }
    
    public function getMaintainAbsoluteLinks()
    {
        return $this->getProperty()->maintainAbsoluteLinks;
    }
    
    public function getMetadataSet()
    {
    	return $this->getContentType()->getMetadataSet();
    }
    
    public function getMetadataSetId()
    {
    	return $this->getContentType()->getMetadataSet()->getId();
    }
    
    public function getMetadataSetPath()
    {
    	return $this->getContentType()->getMetadataSet()->getPath();
    }
    
    public function getMetadataStdClass()
    {
        return $this->getMetadata()->toStdClass();
    }
    
    public function getNodeType( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getNodeType( $identifier );
    }

    public function getNumberOfSiblings( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        if( trim( $identifier ) == "" )
        {
            throw new EmptyValueException( M::EMPTY_IDENTIFIER );
        }
        
        if( !$this->hasIdentifier( $identifier ) )
        {
            throw new NodeException( "The node $identifier does not exist" );
        }
        return $this->structured_data->getNumberOfSiblings( $identifier );
    }

    public function getPageConfigurationSet()
    {
        // the page does not store page configuration set info
        return $this->content_type->getPageConfigurationSet();
    }
    
    public function getPageId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getPageId( $identifier );
    }
    
    public function getPageLevelRegionBlockFormat()
    {
        $configuration = $this->getContentType()->getConfigurationSet()->getDefaultConfiguration();
        return $this->getBlockFormatMap( $configuration );
    }
    
    public function getPagePath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getPagePath( $identifier );
    }
    
    public function getPageRegionNames( $config_name )
    {
        if( !isset( $this->page_configuration_map[ $config_name ] ) )
        {
            throw NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
        }
        
        return $this->page_configuration_map[ $config_name ]->getPageRegionNames();
    }
    
    public function getParentFolder()
    {
    	return $this->getAsset( $this->getService(), Folder::TYPE, $this->getParentFolderId() );
    }

    public function getParentFolderId()
    {
    	return $this->getProperty()->parentFolderId;
    }

    public function getParentFolderPath()
    {
    	return $this->getProperty()->parentFolderPath;
    }
    
    public function getPossibleValues( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getPossibleValues( $identifier );
    }

    public function getShouldBeIndexed()
    {
        return $this->getProperty()->shouldBeIndexed;
    }
    
    public function getShouldBePublished()
    {
        return $this->getProperty()->shouldBePublished;
    }
    
    public function getStructuredData()
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data;
    }
    
    public function getSymlinkId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getSymlinkId( $identifier );
    }
    
    public function getSymlinkPath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getSymlinkPath( $identifier );
    }
    
    public function getText( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->getText( $identifier );
    }
    
    public function getTextNodeType( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
        }
        
        return $this->structured_data->getTextNodeType( $identifier );
    }

    public function getWorkflow()
    {
        $service = $this->getService();
        $service->readWorkflowInformation( $service->createId( self::TYPE, $this->getProperty()->id ) );
        
        if( $service->isSuccessful() )
        {
            if( $service->getReply()->readWorkflowInformationReturn->workflow != NULL )
                return new Workflow( $service->getReply()->readWorkflowInformationReturn->workflow, $service );
            else
                return NULL; // no workflow
        }
        else
        {
            throw new NullAssetException( M::READ_WORKFLOW_FAILURE );
        }
    }
    
    public function getXhtml()
    {
        return $this->getProperty()->xhtml;
    }
    
    public function hasConfiguration( $config_name )
    {
    	return isset( $this->page_configuration_map[ $config_name ] );
    }
    
    public function hasIdentifier( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->hasNode( $identifier );
    }
    
    public function hasNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->hasNode( $identifier );
    }
    
    public function hasPageConfiguration( $config_name )
    {
    	return $this->hasConfiguration( $config_name );
    }
    
    public function hasPageRegion( $config_name, $region_name )
    {
        return $this->hasConfiguration( $config_name ) &&
        	$this->page_configuration_map[ $config_name ]->
            hasPageRegion( $region_name );
    }
    
    public function hasStructuredData()
    {
        return $this->structured_data != NULL;
    }
    
    public function isAssetNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->isAssetNode( $identifier );
    }
    
    public function isGroupNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->isGroupNode( $identifier );
    }
    
    public function isMultiLineNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->isMultiLineNode( $identifier );
    }
    
    public function isMultiple( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->isMultiple( $identifier );
    }
    
    public function isPublishable()
    {
        $parent = $this->getAsset( $this->getService(), Folder::TYPE, $this->getParentContainerId() );
        return $parent->isPublishable() && $this->getShouldBePublished();
    }
    
    public function isRequired( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->isRequired( $identifier );
    }

    public function isTextNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->isTextNode( $identifier );
    }
    
    public function isWYSIWYG( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->isWYSIWYG( $identifier );
    }
    
    public function publish( Destination $destination=NULL )
    {
        if( $destination != NULL )
        {
            $destination_std           = new stdClass();
            $destination_std->id       = $destination->getId();
            $destination_std->type     = $destination->getType();
        }
        
        if( $this->getProperty()->shouldBePublished )
        {
            $service = $this->getService();
            $service->publish( 
                $service->createId( $this->getType(), $this->getId() ), $destination_std );
        }
        return $this;
    }

    public function removeLastSibling( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->removeLastSibling( $identifier );
        $this->edit();
        return $this;
    }
    
    public function replaceByPattern( $pattern, $replace, $include=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->replaceByPattern( $pattern, $replace, $include );
        return $this;
    }
    
    public function replaceXhtmlByPattern( $pattern, $replace )
    {
        if( $this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
        }
        
        $this->xhtml = preg_replace( $pattern, $replace, $this->xhtml );
        
        return $this;
    }
    
    public function replaceText( $search, $replace, $include=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->replaceText( $search, $replace, $include );
        return $this;
    }
    
    public function searchText( $string )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        return $this->structured_data->searchText( $string );
    }
    
    public function searchXhtml( $string )
    {
        if( $this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }

        return strpos( $this->xhtml, $string ) !== false;
    }

    public function setBlock( $identifier, Block $block=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->setBlock( $identifier, $block );
        return $this;
    }
    
    public function setContentType( ContentType $c, $exception=true )
    {
       	// nothing to do
        if( $c->getId() == $this->getContentType()->getId() )
        {
            echo "Nothing to do" . BR;
            return $this;
        }
    
        // part 1: get the page level blocks and formats
        $block_format_array = $this->getPageLevelRegionBlockFormat();
        
        $default_configuration       = $this->getContentType()->
        	getConfigurationSet()->getDefaultConfiguration();
        $default_configuration_name  = $default_configuration->getName();
        $default_config_page_regions = 
        	$default_configuration->getPageRegions();
        $default_region_names        = 
        	$default_configuration->getPageRegionNames();
        
        $page_level_config  = 
        	$this->page_configuration_map[ $default_configuration_name ];
        $page_level_regions = $page_level_config->getPageRegions();
        $page_region_names  = $page_level_config->getPageRegionNames();
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $block_format_array ); }
        
        // part 2: switch content type
        if( $c == NULL )
            throw new NullAssetException( M::NULL_ASSET );

        $page = $this->getProperty();
        $page->contentTypeId      = $c->getId();
        $page->contentTypePath    = $c->getPath();
        
        $configuration_array = array();
        $new_configurations = $c->getPageConfigurationSet()->
        	getPageConfigurations();
        
        foreach( $new_configurations as $new_configuration )
        {
            $configuration_array[] = $new_configuration->toStdClass();
        }
        
        $page->pageConfigurations->pageConfiguration = $configuration_array;
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $page->pageConfigurations ); }
        
        $asset = new stdClass();
        $asset->{ $p = $this->getPropertyName() } = $page;
        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        
        if( !$service->isSuccessful() )
        {
            throw new EditingFailureException( 
                M::EDIT_ASSET_FAILURE . $service->getMessage() );
        }
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $this->getProperty()->pageConfigurations ); }
        
        $this->reloadProperty();
        $this->processPageConfigurations( 
        	$this->getProperty()->pageConfigurations->pageConfiguration );
        
        $this->content_type = $c;
        parent::setPageContentType( $this->content_type );
        
            
        if( $this->getProperty()->structuredData != NULL )
        {
            $this->data_definition_id = $this->content_type->getDataDefinitionId();
            

            // structuredDataNode could be empty for xml pages
            if( isset( $this->getProperty()->structuredData )  &&
            	isset( $this->getProperty()->structuredData->structuredDataNodes ) &&
            	isset( $this->getProperty()->structuredData->structuredDataNodes->structuredDataNode ) 
            )
            {
            	if( $exception )
                	$this->processStructuredData( $this->data_definition_id );
            }
        }
        else
        {
            $this->xhtml = $this->getProperty()->xhtml;
        }
        

        // part 3: plug the blocks and formats back in
        $count = count( array_keys( $block_format_array) );
        
        if( $count > 0 )
        {
            $service = $this->getService();
            $page_level_config  = 
            	$this->page_configuration_map[ $default_configuration_name ];
            $page_region_names  = $page_level_config->getPageRegionNames();
            
            if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $page_region_names ); }
            
            foreach( $block_format_array as $region => $block_format )
            {
                // only if the region exists in the current config
                if( in_array( $region, $page_region_names ) )
                {
                    if( isset( $block_format[ 'block' ] ) )
                    {
                        $block_id = $block_format[ 'block' ];
                    }
                    if( isset( $block_format[ 'format' ] ) )
                    {
                        $format_id = $block_format[ 'format' ];
                    }
                
                    if( isset( $block_id ) )
                    {
                        $block = $this->getAsset( 
                        	$service, $service->getType( $block_id ), $block_id );
                        $this->setRegionBlock( 
                        	$default_configuration_name, $region, $block );
                    }
                    else if( isset( $block_format[ 'no-block' ] ) )
                    {
                        $this->setRegionNoBlock( 
                        	$default_configuration_name, $region, true );
                    }
                
                    if( isset( $format_id ) )
                    {
                        $format = $this->getAsset( 
                        	$service, $service->getType( $format_id ), $format_id );
                        $this->setRegionFormat( 
                        	$default_configuration_name, $region, $format );
                    }
                    else if( isset( $block_format[ 'no-format' ] ) )
                    {
                        $this->setRegionNoFormat( 
                        	$default_configuration_name, $region, true );
                    }
                }
            }
            
            if( $exception )
            	$this->edit();
            else
            	$this->editWithoutException();
        }

        $page  = $this->getProperty();
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $page->pageConfigurations ); }

        return $this;
    }
    
    public function setFile( $identifier, File $file=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->setFile( $identifier, $file );
        return $this;
    }
    
    public function setLinkable( $identifier, Linkable $linkable=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->setLinkable( $identifier, $linkable );
        return $this;
    }
    
    public function setMaintainAbsoluteLinks( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean" );
        
        $this->getProperty()->maintainAbsoluteLinks = $bool;
        
        return $this;
    }
    
    public function setMetadata( stdClass $m )
    {
        $page = $this->getProperty();
        $page->metadata = $m;
        
        $asset = new stdClass();
        $asset->{ $p = $this->getPropertyName() } = $page;
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
    
    public function setPage( $identifier, Page $page=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->setPage( $identifier, $page );
        return $this;
    }
    
    public function setRegionBlock( $config_name, $region_name, Block $block=NULL )
    {
        if( !isset( $this->page_configuration_map[ $config_name ] ) )
        {
            throw new NoSuchPageConfigurationException(
            	"<span class='text_red bold'>Path: " . $this->getPath() . "</span>" . BR .
            	"The page configuration $config_name does not exist." 
            );
        }
    
        if( self::DEBUG )
        {
            DebugUtility::out( "Setting block to region" . BR . "Region name: " . $region_name );
            if( $block != NULL )
                DebugUtility::out( "Block ID: " . $block->getId() );
            else
                DebugUtility::out( "No block passed in." );
        }
        
        $this->page_configuration_map[ $config_name ]->setRegionBlock( $region_name, $block );
        
        return $this;
    }
    
    public function setRegionFormat( $config_name, $region_name, Format $format=NULL )
    {
        if( !isset( $this->page_configuration_map[ $config_name ] ) )
        {
            throw new NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
        }
    
        $this->page_configuration_map[ $config_name ]->setRegionFormat( $region_name, $format );
        
        return $this;
    }
    
    public function setRegionNoBlock( $config_name, $region_name, $no_block )
    {
        if( !isset( $this->page_configuration_map[ $config_name ] ) )
        {
            throw new NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
        }
    
        $this->page_configuration_map[ $config_name ]->setRegionNoBlock( $region_name, $no_block );
        
        return $this;
    }
    
    public function setRegionNoFormat( $config_name, $region_name, $no_format )
    {
        if( !isset( $this->page_configuration_map[ $config_name ] ) )
        {
            throw new NoSuchPageConfigurationException( "The page configuration $config_name does not exist." );
        }
    
        $this->page_configuration_map[ $config_name ]->setRegionNoFormat( $region_name, $no_format );
        
        return $this;
    }
    
    public function setShouldBeIndexed( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean" );
            
        $this->getProperty()->shouldBeIndexed = $bool;
        return $this;
    }
    
    public function setShouldBePublished( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean" );
            
        $this->getProperty()->shouldBePublished = $bool;
        return $this;
    }
    
    public function setStructuredData( StructuredData $structured_data )
    {
        $this->structured_data = $structured_data;
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $structured_data ); }
        
        $this->edit();
        $dd_id = $this->getDataDefinition()->getId();
        $this->processStructuredData( $dd_id );
        return $this;
    }

    public function setSymlink( $identifier, Symlink $symlink=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->setSymlink( $identifier, $symlink );
        return $this;
    }
    
    public function setText( $identifier, $text )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_DATA_DEFINITION_PAGE );
        }
        
        $this->structured_data->setText( $identifier, $text );
        return $this;
    }
    
    public function setXhtml( $xhtml )
    {
        if( !$this->hasStructuredData() )
        {
            $this->xhtml = $xhtml;
        }
        else
        {
            throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
        }
        return $this;
    }
    
    public function swapData( $identifier1, $identifier2 )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongPageTypeException( M::NOT_XHTML_PAGE );
        }
        
        $this->structured_data->swapData( $identifier1, $identifier2 );
        $this->edit()->processStructuredData( $this->data_definition_id );

        return $this;
    }
    
    // to bypass processStructuredData
    private function editWithoutException()
    {
    	return $this->edit( NULL, NULL, "", "", false );
    }

    private function processPageConfigurations( $page_config_std )
    {
        $this->page_configurations = array();
        
        if( !is_array( $page_config_std ) )
        {
            $page_config_std = array( $page_config_std );
        }
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $page_config_std ); }
        
        foreach( $page_config_std as $pc_std )
        {
            $pc = new PageConfiguration( $pc_std, $this->getService(), self::TYPE );
            $this->page_configurations[] = $pc;
            $this->page_configuration_map[ $pc->getName() ] = $pc;
        }
    }

    private function processStructuredData( $data_definition_id )
    {
        $this->structured_data = new StructuredData( 
            $this->getProperty()->structuredData, 
            $this->getService(),
            $data_definition_id
        );
    }

    private $structured_data;
    private $page_configurations; // an array of objects
    private $page_configuration_map;
    private $data_definition_id;
    private $content_type;
}