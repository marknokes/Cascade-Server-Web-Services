<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/5/2015 Added $seconds to copySite, and changed the returned object to $this to avoid the exception.
  * 5/4/2015 Added moveAsset and renameAsset.
  * 10/3/2014 Added getGroupsByName and getUsersByName.
  * 8/26/2014 Fixed a bug in getUsers.
  * 8/7/2014 Fixed a bug in createFolder.
  * 8/1/2014 Fixed a bug in getAudits.
  * 7/23/2014 Added getAssetByIdString.
  * 7/16/2014 Started using DebugUtility::out and DebugUtility::dump.
  * 7/11/2014 Added deleteX to __call. Added getBaseFolderAssetTree.
  * 7/10/2014 Added createFormat, createIndexBlock, createPage, and createXhtmlDataDefinitionBlock.
  * 7/10/2014 Fixed a bug in createPage. Added __call.
  * 7/9/2014 Finished all createX methods.
  * 7/7/2014 Modified createAsset to take care of roles.
  * 7/3/2014 Added createPageConfigurationSet.
  * 7/2/2014 Continued to add createX methods, 24 so far.
  * 6/23/2014 Started adding createX methods.
  * 6/10/2014 Added deleteAsset.
  * 6/9/2014 Fix a bug in getSite.
  * 6/2/2014 Added deleteExpirationMessages.
  * 5/22/2014 Fixed some bugs.
  * 5/21/2014 Added message related methods.
  * 5/14/2014 Added search methods.
  * 5/14/2014 Added checkIn and checkOut.
  * 5/12/2014 Added getAudits.
 */
class Cascade
{
    const DEBUG = false;
    const DUMP  = false;

    public function __construct( AssetOperationHandlerService $service )
    {
        try
        {
            $this->service = $service;
            //if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $service ); }
        }
        catch( Exception $e )
        {
            echo S_PRE . $e . E_PRE;
        }
    }
    
    function __call( $func, $params )
    {
    	$delete = false;
    	
        // derive the class name from method name
        if( strpos( $func, 'get' ) === 0 )
        {
            $class = substr( $func, 3 );
        }
        else if( strpos( $func, 'delete' ) === 0 )
        {
            $class  = substr( $func, 6 );
            $delete = true;
        }
        else
        {
        	throw new NoSuchMethodException( 
        		"<span style='color:red;font-weight:bold;'>The method Cascade::$func does not exist.</span>" );
        }
        
        if( isset( $class ) )
        {
        	$type = $class . "::TYPE";
        	
        	if( !defined( $type ) )
				throw new NoSuchTypeException( 
					"<span style='color:red;font-weight:bold;'>Class $class has no constant TYPE defined.</span>" );

			try
			{
				// get the id/path and site name
                $param0 = NULL;
                $param1 = NULL;

				if( is_array( $params ) && count( $params ) > 0 )
				{
					$param0 = $params[ 0 ]; // id or path
				
					if( isset( $params[ 1 ] ) )
					{
						$param1 = $params[ 1 ]; // site name
					}
				}
				// delete
				if( $delete )
				{
					try
					{
						$this->service->delete( 
							$this->service->createId( $class::TYPE, $param0, $param1 ) );
						return $this;
					}
					catch( Exception $e )
					{
						DebugUtility::out( $e->getMessage() . ' Deletion failed.' );
					}
				}
				// get
				else
				{
					return $this->getAsset( $class::TYPE, $param0, $param1 );
				}
			}
			// gobble the exception
			catch( NullAssetException $e )
			{
				if( $delete )
					return $this;
				else
					return NULL;
			}
		}
		else
		{
			if( $delete )
				return $this;
			else
				return NULL;
		}
    }
    
    public function checkIn( Asset $a, $comments='' )
    {
        if( $a == NULL )
        {
            throw new NullAssetException( M::NULL_ASSET );
        }
        
        if( !is_string( $comments ) )
        {
            throw new Exception( COMMENT_NOT_STRING );
        }
        
        $this->service->checkIn( $a->getIdentifier(), $comments );
        return $this;
    }
    
    public function checkOut( Asset $a )
    {
        if( $a == NULL )
        {
            throw new NullAssetException( M::NULL_ASSET );
        }
        
        $this->service->checkOut( $a->getIdentifier() );
        return $this;
    }
    
    public function clearPermissions( $type, $id_path, $site_name=NULL, $applied_to_children=false )
    {
        $ari = $this->getAccessRights( $type, $id_path, $site_name );
        $ari->clearPermissions();
        $this->setAccessRights( $ari, $applied_to_children );
        return $this;
    }
    
    public function copySite( Site $s, $new_name, $seconds=100 )
    {
    	if( !is_numeric( $seconds ) || !$seconds > 0 )
    		throw new UnacceptableValueException( M::UNACCEPTABLE_SECONDS );
    		
        $this->service->siteCopy( $s->getId(), $s->getName(), $new_name );
        // wait until it is done
        sleep( $seconds );
        
        if( $this->service->isSuccessful() )
        {
            return $this;
        }
        
        throw new SiteCreationFailureException( 
            SITE_CREATION_FAILURE . $this->service->getMessage() );
    }
    
    /* the create group */
    public function createAssetFactory( 
    	AssetFactoryContainer $parent, $name, $type, $mode=T::NONE )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_ASSET_FACTORY_NAME );
    		
    	$asset                                    = AssetTemplate::getAssetFactory();
    	$asset->assetFactory->name                = $name;
    	$asset->assetFactory->parentContainerPath = $parent->getPath();
    	$asset->assetFactory->siteName            = $parent->getSiteName();
    	$asset->assetFactory->assetType           = $type;
    	$asset->assetFactory->workflowMode        = $mode;
    	
    	return $this->createAsset(
    		$asset, AssetFactory::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createAssetFactoryContainer( AssetFactoryContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_ASSET_FACTORY_CONTAINER_NAME );
    	
    	$property = T::$type_property_name_map[ AssetFactoryContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, AssetFactoryContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createConnectorContainer( ConnectorContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONNECTOR_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ ConnectorContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, ConnectorContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createContentType( 
    	ContentTypeContainer $parent, 
    	$name, 
    	PageConfigurationSet $pcs,
    	MetadataSet $ms,
    	DataDefinition $dd=NULL )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_ASSET_FACTORY_NAME );
    		
    	$asset                                        = AssetTemplate::getContentType();
    	$asset->contentType->name                     = $name;
    	$asset->contentType->parentContainerPath      = $parent->getPath();
    	$asset->contentType->siteName                 = $parent->getSiteName();
    	$asset->contentType->pageConfigurationSetPath = $pcs->getPath();
    	$asset->contentType->metadataSetPath          = $ms->getPath();
    	
    	if( isset( $dd ) )
    		$asset->contentType->dataDefinitionPath   = $dd->getPath();
    	
    	return $this->createAsset(
    		$asset, ContentType::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createContentTypeContainer( ContentTypeContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONTENT_TYPE_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ ContentTypeContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, ContentTypeContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createContentTypeIndexBlock( Folder $parent, $name, ContentType $ct=NULL,
    	$max_rendered_assets=0 )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_BLOCK_NAME );
    		
    	$asset                                    = AssetTemplate::getIndexBlock( T::CONTENTTYPEINDEX );
    	$asset->indexBlock->name                  = $name;
    	$asset->indexBlock->parentFolderPath      = $parent->getPath();
    	$asset->indexBlock->siteName              = $parent->getSiteName();
    	if( $ct != NULL )
    		$asset->indexBlock->indexedContentTypeId  = $ct->getId();
    	$asset->indexBlock->maxRenderedAssets     = $max_rendered_assets;
    	$asset->indexBlock->renderingBehavior     = "render-normally";
    	$asset->indexBlock->indexPages            = false;
    	$asset->indexBlock->indexBlocks           = false;
    	$asset->indexBlock->indexLinks            = false;
    	$asset->indexBlock->indexFiles            = false;
    	$asset->indexBlock->indexRegularContent   = false;
    	$asset->indexBlock->indexSystemMetadata   = false;
    	$asset->indexBlock->indexUserMetadata     = false;
    	$asset->indexBlock->indexAccessRights     = false;
    	$asset->indexBlock->indexUserInfo         = false;
    	$asset->indexBlock->indexWorkflowInfo     = false;
    	$asset->indexBlock->appendCallingPageData = false;
    	$asset->indexBlock->sortMethod            = T::ALPHABETICAL;
    	$asset->indexBlock->sortOrder             = T::DESCENDING;
    	$asset->indexBlock->pageXML               = T::NORENDER;
    	
    	return $this->createAsset(
    		$asset, IndexBlock::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createDatabaseTransport( 
    	TransportContainer $parent, $name, $server, $port, 
    	$username, $database, $transport )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_TRANSPORT_NAME );
    	if( trim( $server ) == "" )
    		throw new CreationErrorException( M::EMPTY_SERVER_NAME );
    	if( trim( $port ) == "" )
    		throw new CreationErrorException( M::EMPTY_SERVER_PORT );
    	if( trim( $username ) == "" )
    		throw new CreationErrorException( M::EMPTY_USER_NAME );
    	if( trim( $database ) == "" )
    		throw new CreationErrorException( M::EMPTY_DATABASE_NAME );
    	if( trim( $transport ) == "" )
    		throw new CreationErrorException( M::EMPTY_TRANSPORT_SITE_ID );
    		
    	$asset                                         = AssetTemplate::getDatabaseTransport();
    	$asset->databaseTransport->name                = $name;
    	$asset->databaseTransport->siteName            = $parent->getSiteName();
    	$asset->databaseTransport->parentContainerPath = $parent->getPath();
    	$asset->databaseTransport->username            = trim( $username );
    	$asset->databaseTransport->serverName          = trim( $server );
    	$asset->databaseTransport->serverPort          = trim( $port );
    	$asset->databaseTransport->databaseName        = trim( $database );
    	$asset->databaseTransport->transportSiteId     = trim( $transport );
    	
    	return $this->createAsset(
    		$asset, DatabaseTransport::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createDataDefinition( DataDefinitionContainer $parent, $name, $xml )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_DATA_DEFINITION_NAME );
    		
    	if( trim( $xml ) == "" )
    		throw new CreationErrorException( M::EMPTY_XML );
    		
    	$asset                                      = AssetTemplate::getDataDefinition();
    	$asset->dataDefinition->name                = $name;
    	$asset->dataDefinition->parentContainerPath = $parent->getPath();
    	$asset->dataDefinition->siteName			= $parent->getSiteName();
    	$asset->dataDefinition->xml                 = $xml;
    	
    	return $this->createAsset(
    		$asset, DataDefinition::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createDataDefinitionBlock( Folder $parent, $name, DataDefinition $d )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_BLOCK_NAME );

    	$asset                                             = AssetTemplate::getDataDefinitionBlock();
    	$asset->xhtmlDataDefinitionBlock->name             = $name;
    	$asset->xhtmlDataDefinitionBlock->parentFolderPath = $parent->getPath();
    	$asset->xhtmlDataDefinitionBlock->siteName         = $parent->getSiteName();
    	$asset->xhtmlDataDefinitionBlock->structuredData   = $d->getStructuredData();
    	
    	return $this->createAsset(
    		$asset, DataDefinitionBlock::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createDataDefinitionContainer( DataDefinitionContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_DATA_DEFINITION_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ DataDefinitionContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, DataDefinitionContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createDataDefinitionPage( Folder $parent, $name, ContentType $ct )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_PAGE_NAME );

    	$asset                         = AssetTemplate::getDataDefinitionPage();
    	$asset->page->name             = $name;
    	$asset->page->parentFolderPath = $parent->getPath();
    	$asset->page->siteName         = $parent->getSiteName();
    	$asset->page->contentTypeId    = $ct->getId(); // could be from a different site
    	$asset->page->structuredData   = $ct->getDataDefinition()->getStructuredData();
    		
    	return $this->createAsset(
    		$asset, Page::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createDestination( 
    	SiteDestinationContainer $parent, $name, Transport $transport )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_DESTINATION_NAME );
    		
    	$asset                                   = AssetTemplate::getDestination();
    	$asset->destination->name                = $name;
    	$asset->destination->parentContainerPath = $parent->getPath();
    	
    	$transport_path = $transport->getPath();
    	$transport_site = $transport->getSiteName();
    	
    	// add site name if from Global
    	if( $transport_site == NULL )
    		$transport_path = "Global:" . $transport_path;
    	
    	$asset->destination->transportPath       = $transport_path;
    	$asset->destination->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, Destination::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createFacebookConnector( ConnectorContainer $parent, $name, Destination $d,
    	$pg_value, $px_value,
    	ContentType $ct, $page_config )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONNECTOR_NAME );
    	if( trim( $pg_value ) == "" )
    		throw new CreationErrorException( M::EMPTY_PAGE_NAME );
    	if( trim( $px_value ) == "" )
    		throw new CreationErrorException( M::EMPTY_PREFIX );
    	if( trim( $page_config ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONFIGURATION_NAME );
    		
    	$asset                                         = AssetTemplate::getFacebookConnector();
    	$asset->facebookConnector->name                = $name;
    	$asset->facebookConnector->parentContainerPath = $parent->getPath();
    	$asset->facebookConnector->siteName            = $parent->getSiteName();
    	$asset->facebookConnector->destinationId       = $d->getId();
    	
    	$page_name = new stdClass();
    	$page_name->name = "Page Name";
    	$page_name->value = $pg_value;
    	
    	$prefix = new stdClass();
    	$prefix->name = "Prefix";
    	$prefix->value = $px_value;
    	
    	$asset->facebookConnector->connectorParameters->
    		connectorParameter = array();
		$asset->facebookConnector->connectorParameters->
    		connectorParameter[] = $page_name;
		$asset->facebookConnector->connectorParameters->
    		connectorParameter[] = $prefix;
    	
    	$asset->facebookConnector->connectorContentTypeLinks->
    		connectorContentTypeLink->contentTypeId = $ct->getId();
    	$asset->facebookConnector->connectorContentTypeLinks->
    		connectorContentTypeLink->pageConfigurationName = $page_config;
    	
    	if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $asset ); }
    	return $this->createAsset(
    		$asset, FacebookConnector::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createFeedBlock( Folder $parent, $name, $url )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_BLOCK_NAME );
    		
    	if( trim( $url ) == "" )
    		throw new CreationErrorException( M::EMPTY_TEXT );
    		
    	$asset                              = AssetTemplate::getFeedBlock();
    	$asset->feedBlock->name             = $name;
    	$asset->feedBlock->parentFolderPath = $parent->getPath();
    	$asset->feedBlock->siteName         = $parent->getSiteName();
    	$asset->feedBlock->feedURL          = $url;
    	
    	return $this->createAsset(
    		$asset, FeedBlock::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createFile( Folder $parent, $name, $text="", $data=NULL )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_FILE_NAME );
    		
    	if( trim( $text ) == "" && $data == NULL )
    		throw new CreationErrorException( M::EMPTY_TEXT_DATA );
    		
    	$asset                              = AssetTemplate::getReference();
    	$asset->file->name                = $name;
    	$asset->file->parentFolderPath    = $parent->getPath();
    	$asset->file->siteName            = $parent->getSiteName();
    	
    	if( trim( $text ) != "" )
    		$asset->file->text = trim( $text );
    	else
    		$asset->file->data = $data;
    	
    	return $this->createAsset(
    		$asset, File::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createFileSystemTransport( TransportContainer $parent, $name, $directory )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_TRANSPORT_NAME );
    	if( trim( $directory ) == "" )
    		throw new CreationErrorException( M::EMPTY_DIRECTORY );
    		
    	$asset                                           = AssetTemplate::getFileSystemTransport();
    	$asset->fileSystemTransport->name                = $name;
    	$asset->fileSystemTransport->siteName            = $parent->getSiteName();
    	$asset->fileSystemTransport->parentContainerPath = $parent->getPath();
    	$asset->fileSystemTransport->directory           = $directory;
    	
    	return $this->createAsset(
    		$asset, FileSystemTransport::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createFolder( Folder $parent=NULL, $name="", $site_name="" )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_FOLDER_NAME );
    		
    	if( $parent == NULL && trim( $site_name ) == "" )
    		throw new CreationErrorException( M::EMPTY_SITE_NAME );
    		
    	$asset                               = AssetTemplate::getFolder();
    	$asset->folder->name                 = $name;
    	
    	if( $parent != NULL )
    	{
    		$asset->folder->parentFolderPath = $parent->getPath();
    	}
    		
    	if( $parent != NULL )
    		$site_name = $parent->getSiteName();

   		$asset->folder->siteName = $site_name;
    	
    	return $this->createAsset(
    		$asset, Folder::TYPE, $this->getPath( $parent, $name ), $site_name );
    }
    
    public function createFolderIndexBlock( Folder $parent, $name, Folder $f=NULL,
    	$max_rendered_assets=0 )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_BLOCK_NAME );
    		
    	$asset                                    = AssetTemplate::getIndexBlock( T::FOLDER );
    	$asset->indexBlock->name                  = $name;
    	$asset->indexBlock->parentFolderPath      = $parent->getPath();
    	$asset->indexBlock->siteName              = $parent->getSiteName();
    	$asset->indexBlock->maxRenderedAssets     = $max_rendered_assets;
    	$asset->indexBlock->renderingBehavior     = "render-normally";
    	if( $f != NULL )
    		$asset->indexBlock->indexFolderId     = $f->getId();
    	$asset->indexBlock->indexPages            = false;
    	$asset->indexBlock->indexBlocks           = false;
    	$asset->indexBlock->indexLinks            = false;
    	$asset->indexBlock->indexFiles            = false;
    	$asset->indexBlock->indexRegularContent   = false;
    	$asset->indexBlock->indexSystemMetadata   = false;
    	$asset->indexBlock->indexUserMetadata     = false;
    	$asset->indexBlock->indexAccessRights     = false;
    	$asset->indexBlock->indexUserInfo         = false;
    	$asset->indexBlock->indexWorkflowInfo     = false;
    	$asset->indexBlock->appendCallingPageData = false;
    	$asset->indexBlock->sortMethod            = T::ALPHABETICAL;
    	$asset->indexBlock->sortOrder             = T::DESCENDING;
    	$asset->indexBlock->pageXML               = T::NORENDER;
    	
    	return $this->createAsset(
    		$asset, IndexBlock::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createFormat( Folder $parent, $name, $type, $script="", $xml="" )
    {
    	$type = trim( $type );
    	
    	if( $type != XsltFormat::TYPE && $type != ScriptFormat::TYPE )
    		throw new WrongAssetTypeException( "$type is not a type of format." );

		if( $type == ScriptFormat::TYPE )
			return $this->createScriptFormat( $parent, $name, $script );
		else
			return $this->createXsltFormat( $parent, $name, $xml );
    }
    
    public function createFtpTransport( 
    	TransportContainer $parent, $name, $server, $port, $username, $password )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_TRANSPORT_NAME );
    	if( trim( $server ) == "" )
    		throw new CreationErrorException( M::EMPTY_SERVER_NAME );
    	if( trim( $port ) == "" )
    		throw new CreationErrorException( M::EMPTY_SERVER_PORT );
    	if( trim( $username ) == "" )
    		throw new CreationErrorException( M::EMPTY_USER_NAME );
    	if( trim( $password ) == "" )
    		throw new CreationErrorException( M::EMPTY_PASSWORD );
    		
    	$asset                                    = AssetTemplate::getFtpTransport();
    	$asset->ftpTransport->name                = $name;
    	$asset->ftpTransport->siteName            = $parent->getSiteName();
    	$asset->ftpTransport->parentContainerPath = $parent->getPath();
    	$asset->ftpTransport->username            = trim( $username );
    	$asset->ftpTransport->password            = trim( $password );
    	$asset->ftpTransport->hostName            = trim( $server );
    	$asset->ftpTransport->port                = trim( $port );
    	
    	return $this->createAsset(
    		$asset, FtpTransport::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createGoogleAnalyticsConnector( ConnectorContainer $parent, $name, $profile_id )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONNECTOR_NAME );
    	if( trim( $profile_id ) == "" )
    		throw new CreationErrorException( M::EMPTY_PROFILE_ID );
    		
    	$asset                                                = AssetTemplate::getGoogleAnalyticsConnector();
    	$asset->googleAnalyticsConnector->name                = $name;
    	$asset->googleAnalyticsConnector->parentContainerPath = $parent->getPath();
    	$asset->googleAnalyticsConnector->siteName            = $parent->getSiteName();
    	
    	$param        = new stdClass();
    	$param->name  = "Google Analytics Profile Id";
    	$param->value = $profile_id;
    	$asset->googleAnalyticsConnector->
    		connectorParameters->connectorParameter = array();
    	$asset->googleAnalyticsConnector->
    		connectorParameters->connectorParameter[ 0 ] = $param;

        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $asset ); }
    	return $this->createAsset(
    		$asset, GoogleAnalyticsConnector::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createGroup( $group_name, $role_name='Default' )
    {
    	if( trim( $group_name ) == "" )
    		throw new CreationErrorException( M::EMPTY_GROUP_NAME );
    		
    	$asset                   = AssetTemplate::getGroup();
    	$asset->group->groupName = $group_name;
    	$asset->group->role      = $role_name;
    	
    	return $this->createAsset( $asset, Group::TYPE, $group_name );
    }
    
    public function createIndexBlock( Folder $parent, $name, $type, ContentType $ct=NULL, Folder $f=NULL,
    	$max_rendered_assets=0 )
    {
		if( $type == T::CONTENTTYPEINDEX )
			return $this->createContentTypeIndexBlock( $parent, $name, $ct, $max_rendered_assets );
		else
			return $this->createFolderIndexBlock( $parent, $name, $f, $max_rendered_assets );
    }

    public function createMetadataSet( MetadataSetContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_METADATA_SET_NAME );
    		
    	$asset                                   = AssetTemplate::getMetadataSet();
    	$asset->metadataSet->name                = $name;
    	$asset->metadataSet->parentContainerPath = $parent->getPath();
    	$asset->metadataSet->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, MetadataSet::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createMetadataSetContainer( MetadataSetContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_METADATA_SET_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ MetadataSetContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, MetadataSetContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createPage( Folder $parent, $name, ContentType $ct, $xhtml="" )
    {
    	if( $ct->getDataDefinition() != NULL )
    		return $this->createDataDefinitionPage( $parent, $name, $ct );
    	else
    		return $this->createXhtmlPage( $parent, $name, $xhtml, $ct );
    }

    public function createPageConfigurationSet( 
    	PageConfigurationSetContainer $parent, 
    	$name,        // configuration set name
    	$config_name, // default configuration name
    	Template $t, $extension, $type )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_PAGE_CONFIGURATION_SET_NAME );
    		
    	if( trim( $config_name ) == "" )
    		throw new CreationErrorException( M::EMPTY_PAGE_CONFIGURATION_NAME );

    	if( trim( $extension ) == "" )
    		throw new EmptyValueException( M::EMPTY_FILE_EXTENSION );
    		
    	if( !SerializationTypeValues::isSerializationTypeValue( $type ) )
    		throw new WrongSerializationTypeException( "The serialization type $type is not acceptable. " );
    	
		$config                        = AssetTemplate::getPageConfiguration();
		$config->name                  = $config_name;
		$config->defaultConfiguration  = true;
		$config->templateId            = $t->getId();
		$config->templatePath          = $t->getPath();
		$config->pageRegions           = $t->getPageRegionStdForPageConfiguration();
		$config->outputExtension       = $extension;
		$config->serializationType     = $type;
    	
    	$asset                                            = AssetTemplate::getPageConfigurationSet();
    	$asset->pageConfigurationSet->name                = $name;
    	$asset->pageConfigurationSet->parentContainerPath = $parent->getPath();
    	$asset->pageConfigurationSet->siteName            = $parent->getSiteName();
    	$asset->pageConfigurationSet->pageConfigurations->pageConfiguration = $config;
    	
    	return $this->createAsset(
    		$asset, PageConfigurationSet::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createPageConfigurationSetContainer( PageConfigurationSetContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_PAGE_CONFIGURATION_SET_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ PageConfigurationSetContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, PageConfigurationSetContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createPublishSet( PublishSetContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_PUBLISH_SET_NAME );
    		
    	$asset                                  = AssetTemplate::getPublishSet();
    	$asset->publishSet->name                = $name;
    	$asset->publishSet->parentContainerPath = $parent->getPath();
    	$asset->publishSet->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, PublishSet::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }

    public function createPublishSetContainer( PublishSetContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_PUBLISH_SET_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ PublishSetContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, PublishSetContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createReference( Asset $a, Folder $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_REFERENCE_NAME );
    		
    	$asset                                 = AssetTemplate::getReference();
    	$asset->reference->name                = $name;
    	$asset->reference->parentFolderPath    = $parent->getPath();
    	$asset->reference->siteName            = $parent->getSiteName();
    	$asset->reference->referencedAssetType = $a->getType();
    	$asset->reference->referencedAssetId   = $a->getId();
    	
    	return $this->createAsset(
    		$asset, Reference::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createRole( $name, $type )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_ROLE_NAME );
    	if( !RoleTypeValues::isRoleTypeValue( trim( $type ) ) )
    		throw new CreationErrorException( "Unacceptable role type $type." );
    		
    	$asset                 = AssetTemplate::getRole();
    	$asset->role->name     = $name;
    	$asset->role->roleType = $type;
    	
    	if( $type == Site::TYPE )
    		$asset->role->siteAbilities   = new stdClass();
    	else
    		$asset->role->globalAbilities = new stdClass();
    	
    	return $this->createAsset( $asset, Role::TYPE, $name );
    }
    
    public function createScriptFormat( Folder $parent, $name, $script )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_FORMAT_NAME );
    		
    	if( trim( $script ) == "" )
    		throw new CreationErrorException( M::EMPTY_SCRIPT );
    		
    	$asset                                 = AssetTemplate::getFormat( P::SCRIPTFORMAT );
    	$asset->scriptFormat->name             = $name;
    	$asset->scriptFormat->parentFolderPath = $parent->getPath();
    	$asset->scriptFormat->siteName         = $parent->getSiteName();
    	$asset->scriptFormat->script           = $script;
    	
    	return $this->createAsset(
    		$asset, ScriptFormat::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createSite( $name, $url, $recycle_bin_expiration )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_SYMLINK_NAME );
    		
    	if( trim( $url ) == "" )
    		throw new CreationErrorException( M::EMPTY_URL );
    		
    	
    	if( trim( $recycle_bin_expiration ) == "" || 
    		!RecycleBinExpirationValues::isRecycleBinExpirationValue( trim( $recycle_bin_expiration ) ) )
    		throw new CreationErrorException( M::EMPTY_RECYCLE_BIN_EXPIRATION );
    		
    	$asset              = AssetTemplate::getSite();
    	$asset->site->name  = $name;
    	$asset->site->url   = $url;
    	$asset->site->recycleBinExpiration = $recycle_bin_expiration;
    	
    	$site = $this->createAsset( $asset, Site::TYPE, $name );
    	$site->setUrl( $url )->setRecycleBinExpiration( $recycle_bin_expiration )->edit();
    	
    	return $site;
    }
    
    public function createSiteDestinationContainer( SiteDestinationContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_SITE_DESTINATION_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ SiteDestinationContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, SiteDestinationContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createSymlink( Folder $parent, $name, $url )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_SYMLINK_NAME );
    		
    	if( trim( $url ) == "" )
    		throw new CreationErrorException( M::EMPTY_URL );
    		
    	$asset                            = AssetTemplate::getSymlink();
    	$asset->symlink->name             = $name;
    	$asset->symlink->parentFolderPath = $parent->getPath();
    	$asset->symlink->siteName         = $parent->getSiteName();
    	$asset->symlink->linkURL          = $url;
    	
    	return $this->createAsset(
    		$asset, Symlink::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createTemplate( Folder $parent, $name, $xml )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_TEMPLATE_NAME );
    		
    	if( trim( $xml ) == "" )
    		throw new CreationErrorException( M::EMPTY_XML );
    		
    	$asset                              = AssetTemplate::getTemplate();
    	$asset->template->name              = $name;
    	$asset->template->parentFolderPath  = $parent->getPath();
    	$asset->template->siteName          = $parent->getSiteName();
    	$asset->template->xml               = trim( $xml );
    	
    	return $this->createAsset(
    		$asset, Template::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createTextBlock( Folder $parent, $name, $text )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_BLOCK_NAME );
    		
    	if( trim( $text ) == "" )
    		throw new CreationErrorException( M::EMPTY_TEXT );
    		
    	$asset                              = AssetTemplate::getTextBlock();
    	$asset->textBlock->name             = $name;
    	$asset->textBlock->parentFolderPath = $parent->getPath();
    	$asset->textBlock->siteName         = $parent->getSiteName();
    	$asset->textBlock->text             = $text;
    	
    	return $this->createAsset(
    		$asset, TextBlock::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createTransportContainer( TransportContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_TRANSPORT_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ TransportContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, TransportContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createTwitterConnector( ConnectorContainer $parent, $name, Destination $d,
    	$ht_value, $px_value,
    	ContentType $ct, $page_config )
    {
    	if( self::DEBUG ) { DebugUtility::out( "Hash tag: $ht_value Prefix: $px_value" ); }
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONNECTOR_NAME );
    	if( trim( $ht_value ) == "" )
    		throw new CreationErrorException( M::EMPTY_PAGE_NAME );
    	if( trim( $px_value ) == "" )
    		throw new CreationErrorException( M::EMPTY_PREFIX );
    	if( trim( $page_config ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONFIGURATION_NAME );
    		
    	$asset                                         = AssetTemplate::getTwitterConnector();
    	$asset->twitterConnector->name                = $name;
    	$asset->twitterConnector->parentContainerPath = $parent->getPath();
    	$asset->twitterConnector->siteName            = $parent->getSiteName();
    	$asset->twitterConnector->destinationId       = $d->getId();
    	
    	$ht_name = new stdClass();
    	$ht_name->name = "Hash Tag";
    	$ht_name->value = $pg_value;
    	
    	$prefix = new stdClass();
    	$prefix->name = "Prefix";
    	$prefix->value = $px_value;
    	
    	$asset->twitterConnector->connectorParameters->
    		connectorParameter = array();
		$asset->twitterConnector->connectorParameters->
    		connectorParameter[] = $ht_name;
		$asset->twitterConnector->connectorParameters->
    		connectorParameter[] = $prefix;
    	
    	$asset->twitterConnector->connectorContentTypeLinks->
    		connectorContentTypeLink->contentTypeId = $ct->getId();
    	
    	$asset->twitterConnector->connectorContentTypeLinks->
    		connectorContentTypeLink->pageConfigurationName = $page_config;
    	
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $asset ); }
    	return $this->createAsset(
    		$asset, TwitterConnector::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createUser( $user_name, $password, Group $group, Role $global_role )
    {
    	if( trim( $user_name ) == "" )
    		throw new CreationErrorException( M::EMPTY_USER_NAME );
    		
    	if( trim( $password ) == "" )
    		throw new CreationErrorException( M::EMPTY_PASSWORD );
    		
    	$asset                 = AssetTemplate::getUser();
    	$asset->user->username = $user_name;
    	$asset->user->password = $password;
    	$asset->user->groups   = $group->getId();
    	$asset->user->role     = $global_role->getName();
    	
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $asset ); }
    	return $this->createAsset( $asset, User::TYPE, $user_name );
    }
    
    public function createWordPressConnector( ConnectorContainer $parent, $name, $url,
    	ContentType $ct, $page_config )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONNECTOR_NAME );
    	if( trim( $url ) == "" )
    		throw new CreationErrorException( M::EMPTY_PAGE_NAME );
    	if( trim( $page_config ) == "" )
    		throw new CreationErrorException( M::EMPTY_CONFIGURATION_NAME );
    		
    	$asset                                          = AssetTemplate::getWordPressConnector();
    	$asset->wordPressConnector->name                = $name;
    	$asset->wordPressConnector->parentContainerPath = $parent->getPath();
    	$asset->wordPressConnector->siteName            = $parent->getSiteName();
    	$asset->wordPressConnector->url                 = trim( $url );
    	
    	$asset->wordPressConnector->connectorContentTypeLinks->
    		connectorContentTypeLink->contentTypeId = $ct->getId();
    	
    	$asset->wordPressConnector->connectorContentTypeLinks->
    		connectorContentTypeLink->pageConfigurationName = $page_config;
    	
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $asset ); }
    	return $this->createAsset(
    		$asset, WordPressConnector::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createWorkflowDefinition( 
    	WorkflowDefinitionContainer $parent, $name, $naming_behavior, $xml )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_BLOCK_NAME );
    		
    	if( trim( $xml ) == "" )
    		throw new CreationErrorException( M::EMPTY_XML );
    		
    	if( !NamingBehaviorValues::isNamingBehaviorValue( $naming_behavior ) )
			throw new UnacceptableValueException( 
				"The naming behavior $naming_behavior is unacceptable. " );
	
    	$asset                                          = AssetTemplate::getWorkflowDefinition();
    	$asset->workflowDefinition->name                = $name;
    	$asset->workflowDefinition->parentContainerPath = $parent->getPath();
    	$asset->workflowDefinition->siteName            = $parent->getSiteName();
    	$asset->workflowDefinition->xml                 = $xml;
    	$asset->workflowDefinition->namingBehavior      = $naming_behavior;
    	$asset->workflowDefinition->copy                = true;
    	
    	return $this->createAsset(
    		$asset, WorkflowDefinition::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createWorkflowDefinitionContainer( WorkflowDefinitionContainer $parent, $name )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_WORKFLOW_DEFINITION_CONTAINER_NAME );
    		
    	$property = T::$type_property_name_map[ WorkflowDefinitionContainer::TYPE ];
    	$asset                                 = AssetTemplate::getContainer( $property );
    	$asset->$property->name                = $name;
    	$asset->$property->parentContainerPath = $parent->getPath();
    	$asset->$property->siteName            = $parent->getSiteName();
    	
    	return $this->createAsset(
    		$asset, WorkflowDefinitionContainer::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createXhtmlBlock( Folder $parent, $name, $xhtml="" )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_BLOCK_NAME );

    	$asset                                             = AssetTemplate::getDataDefinitionBlock();
    	$asset->xhtmlDataDefinitionBlock->name             = $name;
    	$asset->xhtmlDataDefinitionBlock->parentFolderPath = $parent->getPath();
    	$asset->xhtmlDataDefinitionBlock->siteName         = $parent->getSiteName();
    	
    	if( trim( $xhtml ) != "" )
    		$asset->xhtmlDataDefinitionBlock->xhtml        = $xhtml;
    		
    	return $this->createAsset(
    		$asset, DataDefinitionBlock::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createXhtmlDataDefinitionBlock( Folder $parent, $name, DataDefinition $d=NULL, $xhtml="" )
    {
    	if( $d == NULL )
    		return $this->createXhtmlBlock( $parent, $name, $xhtml );
    	else
    		return $this->createDataDefinitionBlock( $parent, $name, $d );
    }
    
    public function createXhtmlPage( Folder $parent, $name, $xhtml="", ContentType $ct )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_PAGE_NAME );

    	$asset                         = AssetTemplate::getXhtmlPage();
    	$asset->page->name             = $name;
    	$asset->page->parentFolderPath = $parent->getPath();
    	$asset->page->siteName         = $parent->getSiteName();
    	$asset->page->contentTypePath  = $ct->getPath();
    	
    	if( trim( $xhtml ) != "" )
    		$asset->page->xhtml = $xhtml;
    		
    	return $this->createAsset(
    		$asset, Page::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createXmlBlock( Folder $parent, $name, $xml )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_BLOCK_NAME );
    		
    	if( trim( $xml ) == "" )
    		throw new CreationErrorException( M::EMPTY_XML );
    		
    	$asset                             = AssetTemplate::getXmlBlock();
    	$asset->xmlBlock->name             = $name;
    	$asset->xmlBlock->parentFolderPath = $parent->getPath();
    	$asset->xmlBlock->siteName         = $parent->getSiteName();
    	$asset->xmlBlock->xml              = $xml;
    	
    	return $this->createAsset(
    		$asset, XmlBlock::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    public function createXsltFormat( Folder $parent, $name, $xml )
    {
    	if( trim( $name ) == "" )
    		throw new CreationErrorException( M::EMPTY_FORMAT_NAME );
    		
    	if( trim( $xml ) == "" )
    		throw new CreationErrorException( M::EMPTY_XML );
    		
    	$asset                               = AssetTemplate::getFormat( P::XSLTFORMAT );
    	$asset->xsltFormat->name             = $name;
    	$asset->xsltFormat->parentFolderPath = $parent->getPath();
    	$asset->xsltFormat->siteName         = $parent->getSiteName();
    	$asset->xsltFormat->xml              = $xml;
    	
    	return $this->createAsset(
    		$asset, XsltFormat::TYPE, $this->getPath( $parent, $name ), $parent->getSiteName() );
    }
    
    /* ================= */
    
    public function deleteAllMessages()
    {
        return $this->deleteMessagesWithIds( 
            MessageArrays::$all_message_ids );
    }
    
    public function deleteAllMessagesWithoutIssues()
    {
        return
            $this->deletePublishMessagesWithoutIssues()->
                   deleteUnpublishMessagesWithoutIssues();
    }
    
    public function deleteAsset( Asset $a )
    {
    	try
    	{
    		$this->service->delete( $this->service->createId( $a->getType(), $a->getId() ) );
    	}
		catch( Exception $e )
		{
			throw new DeletionErrorException( M::DELETE_ASSET_FAILURE . $e );
		}
		unset( $a );
		return $this;
    }
    
    public function deleteExpirationMessages()
    {
        MessageArrays::initialize( $this->service );
        return $this->deleteMessagesWithIds( 
            MessageArrays::$asset_expiration_message_ids );
    }
    
    public function deletePublishMessagesWithoutIssues()
    {
        MessageArrays::initialize( $this->service );
        return $this->deleteMessagesWithIds( 
            MessageArrays::$publish_message_ids_without_issues );
    }
    
    public function deleteSummaryMessagesNoFailures()
    {
        MessageArrays::initialize( $this->service );
        return $this->deleteMessagesWithIds( 
            MessageArrays::$summary_message_ids_no_failures );
    }
    
    public function deleteUnpublishMessagesWithoutIssues()
    {
        MessageArrays::initialize( $this->service );
        return $this->deleteMessagesWithIds( 
            MessageArrays::$unpublish_message_ids_without_issues );
    }
    
    public function deleteWorkflowMessagesIsComplete()
    {
        MessageArrays::initialize( $this->service );
        return $this->deleteMessagesWithIds( 
            MessageArrays::$workflow_message_ids_is_complete );
    }
    
    public function denyAccess( $type, $id_path, $site_name=NULL, $applied_to_children=false, Asset $a=NULL )
    {
        $ari = $this->getAccessRights( $type, $id_path, $site_name );
        
        if( $a == NULL || ( $a->getType() != Group::TYPE && $a->getType() != User::TYPE ) )
        {
            throw new WrongAssetTypeException( M::ACCESS_TO_USERS_GROUPS );
        }
        
        if( $a->getType() == Group::TYPE )
        {
            if( self::DEBUG ) { DebugUtility::out( "Denying " . $a->getName() . " access" ); }
            $func_name = 'denyGroupAccess';
        }
        else
        {
            if( self::DEBUG ) { DebugUtility::out( "Denying " . $a->getName() . " access" ); }
            $func_name = 'denyUserAccess';
        }
        
        $ari->$func_name( $a );
        $this->setAccessRights( $ari, $applied_to_children );
        return $this;
    }
    
    public function denyAllAccess( $type, $id_path, $site_name=NULL, $applied_to_children=false )
    {
        if( self::DEBUG ) { DebugUtility::out( "Denying all access" ); }
        $ari = $this->getAccessRights( $type, $id_path, $site_name );
        $ari->setAllLevel( T::NONE );
        $this->setAccessRights( $ari, $applied_to_children );
        return $this;
    }
    
    public function getAccessRights( $type, $id_path, $site_name=NULL )
    {
    	// to make sure the asset exists
    	$this->getAsset( $type, $id_path, $site_name );
    	
        $this->service->readAccessRights(
            $this->service->createId( $type, $id_path, $site_name ) );
            
        if( $this->service->isSuccessful() )
        {
            return new AccessRightsInformation(
                $this->service->getReadAccessRightInformation() );
        }
        else
        {
            throw new AccessRightsException( $this->service->getMessage() );
        }
    }
    
    public function getAllMessages()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$all_messages;
    }
    
    public function getAsset( $type, $id_path, $site_name=NULL )
    {
        return Asset::getAsset( $this->service, $type, $id_path, $site_name );
    }
    
    public function getAssetByIdString( $id_string )
    {
    	$type = $this->service->getType( $id_string );
    	
    	if( $type != "The id does not match any asset type." )
    	{
    		return $this->getAsset( $type, $id_string );
    	}
    	return NULL;
    }
    
    public function getAudits( 
        Asset $a, $type="", DateTime $start_time=NULL, DateTime $end_time=NULL )
    {
		return $a->getAudits( $type, $start_time, $end_time );
    }
    
    public function getBaseFolderAssetTree( $site_name )
    {
    	return $this->getFolder( '/', $site_name )->getAssetTree();
    }
    
    public function getGroups()
    {
        if( $this->groups == NULL )
        {
            $search_for               = new stdClass();
            $search_for->matchType    = T::MATCH_ANY;
            $search_for->searchGroups = true;
            $search_for->assetName    = '*';
    
            $this->service->search( $search_for );
            
            if ( $this->service->isSuccessful() )
            {
                if( !is_null( $this->service->getSearchMatches()->match ) )
                {
                    $groups = $this->service->getSearchMatches()->match;
                    $this->groups = array();
            		
            		if( count( $groups ) == 1 ) // a string
            			$this->groups[]            = new Identifier( $groups );
            		else
						foreach( $groups as $group )
							$this->groups[] = new Identifier( $group );
                }
            }
        }
        return $this->groups;
    }
    
    public function getGroupsByName( $name="" )
    {
    	if( $name == "" )
    		return $this->getGroups();
    		
    	$group_ids                = array();
		$search_for               = new stdClass();
		$search_for->matchType    = T::MATCH_ANY;
		$search_for->searchGroups = true;
		$search_for->assetName    = $name;

		$this->service->search( $search_for );
		
		if ( $this->service->isSuccessful() )
		{
			if( !is_null( $this->service->getSearchMatches()->match ) )
			{
				$groups = $this->service->getSearchMatches()->match;
		
				if( count( $groups ) == 1 )
					$group_ids[] = new Identifier( $groups );
				else
					foreach( $groups as $group )
						$group_ids[] = new Identifier( $group );
			}
		}
		return $group_ids;
    }    
    
    public function getMessage( $id )
    {
        MessageArrays::initialize( $this->service );
    
        if( isset( MessageArrays::$id_obj_map[ $id ] ) )
            return MessageArrays::$id_obj_map[ $id ];
            
        return NULL;
    }
    
    public function getMessageIdObjMap()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$id_obj_map;
    }
    
    public function getPublishMessages()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$publish_messages;
    }
    
    public function getPublishMessagesWithIssues()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$publish_messages_with_issues;
    }
    
    public function getPublishMessagesWithoutIssues()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$publish_messages_without_issues;
    }
    
    public function getRoleAssetById( $role_id )
    {
        if( $this->roles == NULL )
        {
            $this->getRoles();
        }
        
        if( !$this->hasRoleId( $role_id ) )
            throw new NullAssetException( M::WRONG_ROLE );
        
        return $this->role_id_object_map[ $role_id ];
    }
    
    public function getRoleAssetByName( $role_name )
    {
        $this->getRoles();
        
        if( !$this->hasRoleName( $role_name ) )
            throw new NullAssetException( M::WRONG_ROLE );
        
        return $this->role_name_object_map[ $role_name ];
    }
    
    public function getRoleById( $role_id )
    {
    	return $this->getRoleAssetById( $role_id );
    }
    
    public function getRoleByName( $role_name )
    {
    	return $this->getRoleAssetByName( $role_name );
    }
    
    public function getRoleIds()
    {
        if( $this->roles == NULL )
        {
            $this->getRoles();
        }
        return array_keys( $this->role_id_object_map );
    }
    
    public function getRoleNames()
    {
        if( $this->roles == NULL )
        {
            $this->getRoles();
        }
        return array_keys( $this->role_name_object_map );
    }
    
    public function getRoles()
    {
    	// sleep for creation of new roles
    	sleep( 5 );
    
		$this->role_name_object_map = array();
		$this->role_id_object_map   = array();
	
		$search_for              = new stdClass();
		$search_for->matchType   = T::MATCH_ANY;
		$search_for->searchRoles = true;
		$search_for->assetName   = '*';

		$this->service->search( $search_for );
		
		if ( $this->service->isSuccessful() )
		{
			if( !is_null( $this->service->getSearchMatches()->match ) )
			{
				$roles = $this->service->getSearchMatches()->match;
				$this->roles = array();
		
				foreach( $roles as $role )
				{
					$role_identifier = new Identifier( $role );
					$this->roles[]   = $role_identifier;
					$role_object     = $role_identifier->getAsset( $this->service );
					if( self::DEBUG ) { DebugUtility::out( $role_object->getName() ); }
					$this->role_name_object_map[ $role_object->getName() ] = $role_object;
					$this->role_id_object_map[ $role_object->getId() ]     = $role_object;
				}
			}
		}
        return $this->roles;
    }    

    public function getService()
    {
        return $this->service;
    }
    
    public function getSite( $site_name )
    {
        $this->getSites();
        
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $this->name_site_map ); }
        
        if( !isset( $this->name_site_map[ $site_name ] ) )
        {
            throw new NoSuchSiteException( 
            	"<span style='color:red;font-weight:bold;'>The site $site_name does not exist.</span>" );
        }
        
        return Asset::getAsset( $this->service, Site::TYPE, 
            $this->name_site_map[ $site_name ]->getId() );
    }
    
    public function getSites()
    {
        if( $this->sites == NULL )
        {
            $this->service->listSites();
            $this->name_site_map = array();
            
            if( $this->service->isSuccessful() )
            {
                $assetIdentifiers = $this->service->getReply()->listSitesReturn->sites->assetIdentifier;
                
                foreach( $assetIdentifiers as $identifier )
                {
                    $site = new Identifier( $identifier );
                    $this->sites[] = $site;
                    $this->name_site_map[ $identifier->path->path ] = $site;
                }
            }
            else
            {
                throw new Exception( $this->service->getMessage() );
            }
        }
        return $this->sites;
    }
    
    public function getSummaryMessages()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$summary_messages;
    }
    
    public function getSummaryMessagesNoFailures()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$summary_messages_no_failures;
    }
    
    public function getSummaryMessagesWithFailures()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$summary_messages_with_failures;
    }
    
    public function getUnpublishMessages()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$unpublish_messages;
    }
    
    public function getUnpublishMessagesWithIssues()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$unpublish_messages_with_issues;
    }
    
    public function getUnpublishMessagesWithoutIssues()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$unpublish_messages_without_issues;
    }
    
    public function getUsers()
    {
        $user_name_array = array();
        
        // maximally returns 250 users
        if( $this->users == NULL )
        {
            $search_for              = new stdClass();
            $search_for->matchType   = T::MATCH_ANY;
            $search_for->searchUsers = true;
            $search_for->assetName   = '*';
    
            $this->service->search( $search_for );
            
            if ( $this->service->isSuccessful() )
            {
                if( !is_null( $this->service->getSearchMatches()->match ) )
                {
                    $users = $this->service->getSearchMatches()->match;
                    $this->users = array();
            
                    foreach( $users as $user )
                    {
                        $this->users[] = new Identifier( $user );
                        $user_name_array[]  = $user->id;
                    }
                }
            }
        }
        
        // add those that belong to groups
        $extra_names = array();
        $extra_users = array();
        
        if( $this->groups == NULL || count( $this->groups ) == 0 )
        {
        	$this->getGroups();
        }
        
        if( count( $this->groups ) > 0 )
        {
			foreach( $this->groups as $group )
			{
				$users = $group->getAsset( $this->service )->getUsers();
			
				$users = explode( ';', $users ); // array
				
				foreach( $users as $user )
				{
					if( trim( $user ) != "" && !in_array( $user, $user_name_array ) && !in_array( $user, $extra_names ) )
					{
						$user_std       = new stdClass();
						$user_std->id   =  $user;
						$user_std->path = new stdClass();
						$user_std->path->path = NULL;
						$user_std->path->siteName = NULL;
						$user_std->type = User::TYPE;
						$user_std->recycled = false;
						$extra_users[] = new Identifier( $user_std );
						$extra_names[] = $user;
					}
				}
			}
        }
        return array_merge( $this->users, $extra_users );
    }
    
    public function getUsersByName( $name )
    {
    	if( $name == "" )
    		return $this->getUsers();
    		
    	$user_ids                 = array();
		$search_for               = new stdClass();
		$search_for->matchType    = T::MATCH_ANY;
		$search_for->searchUsers  = true;
		$search_for->assetName    = $name;

		$this->service->search( $search_for );
		
		if ( $this->service->isSuccessful() )
		{
			if( !is_null( $this->service->getSearchMatches()->match ) )
			{
				$users = $this->service->getSearchMatches()->match;
		
				if( count( $users ) == 1 )
					$user_ids[] = new Identifier( $users );
				else
					foreach( $users as $user )
						$user_ids[] = new Identifier( $user );
			}
		}
		return $user_ids;
    }    
    
    public function getWorkflowMessages()
    {
        MessageArrays::initialize( $this->service );
        return $workflow_messages;
    }
    
    public function getWorkflowMessagesIsComplete()
    {
        MessageArrays::initialize( $this->service );
        return $workflow_messages_complete;
    }
    
    public function getWorkflowMessagesOther()
    {
        MessageArrays::initialize( $this->service );
        return MessageArrays::$workflow_messages_other;
    }
    
    public function grantAccess( $type, $id_path, $site_name=NULL, $applied_to_children=false, 
        Asset $a=NULL, $level=T::READ )
    {
        $ari = $this->getAccessRights( $type, $id_path, $site_name );
        
        if( $a == NULL || ( $a->getType() != Group::TYPE && $a->getType() != User::TYPE ) )
        {
            throw new WrongAssetTypeException( M::ACCESS_TO_USERS_GROUPS );
        }
        
        if( !LevelValues::isLevel( $level ) )
        {
            throw new UnacceptableValueException( 
            	"<span style='color:red;font-weight:bold;'>The level $level is unacceptable.</span>" );
        }
        
        if( $a->getType() == Group::TYPE && $level == T::READ )
        {
            if( self::DEBUG ) { DebugUtility::out( "Granting " . $a->getName() . " read access to " . $id_path ); }
            $func_name = 'grantGroupReadAccess';
        }
        else if( $a->getType() == Group::TYPE && $level == T::WRITE )
        {
            if( self::DEBUG ) { DebugUtility::out( "Granting " . $a->getName() . " write access to " . $id_path ); }
            $func_name = 'grantGroupWriteAccess';
        }
        else if( $a->getType() == User::TYPE && $level == T::READ )
        {
            if( self::DEBUG ) { DebugUtility::out( "Granting " . $a->getName() . " read access to " . $id_path ); }
            $func_name = 'grantUserReadAccess';
        }
        else if( $a->getType() == User::TYPE && $level == T::WRITE )
        {
            if( self::DEBUG ) { DebugUtility::out( "Granting " . $a->getName() . " write access to " . $id_path ); }
            $func_name = 'grantUserWriteAccess';
        }
        
        if( isset( $func_name ) )
        {
            $ari->$func_name( $a );
            $this->setAccessRights( $ari, $applied_to_children );
        }
        else
        {
            if( self::DEBUG ) { DebugUtility::out( "The function name is not set." ); }
        }
        return $this;
    }
    
    public function hasGroup( $group_name )
    {
    	try
    	{
    		$this->getAsset( Group::TYPE, $group_name );
    		return true;
    	}
    	catch( Exception $e )
    	{
    		echo S_PRE . $e . E_PRE;
    		return false;
    	}
    }
    
    public function hasRoleId( $role_id )
    {
        if( $this->roles == NULL )
        {
            $this->getRoles();
        }
        return in_array( $role_id, array_keys( $this->role_id_object_map ) );
    }

    public function hasRoleName( $role_name )
    {
        if( $this->roles == NULL )
        {
            $this->getRoles();
        }
        return in_array( $role_name, array_keys( $this->role_name_object_map ) );
    }
    
    public function moveAsset( Asset $a, Container $new_parent )
    {
        if( $a == NULL || $new_parent == NULL )
        {
            throw new NullAssetException( M::NULL_ASSET );
        }
        if( $a->getParentContainer()->getId() == $new_parent->getId() )
        {
        	throw new RenamingFailureException( M::SAME_CONTAINER );
        }
        
        $this->service->move( 
        	$a->getIdentifier(),
        	$new_parent->getIdentifier(),
        	$a->getName(),
        	false );
        	
        if( !$this->service->isSuccessful() )
        {
        	throw new RenamingFailureException( M::RENAME_ASSET_FAILURE );
        }
        	
    	return $this;
    }
    
    public function renameAsset( Asset $a, $new_name )
    {
        if( $a == NULL )
        {
            throw new NullAssetException( M::NULL_ASSET );
        }
        if( trim( $new_name ) == "" )
        {
        	throw new EmptyValueException( M::EMPTY_NAME );
        }
        
        $this->service->move( 
        	$a->getIdentifier(),
        	$a->getParentContainer()->getIdentifier(),
        	$new_name,
        	false );
        	
        if( !$this->service->isSuccessful() )
        {
        	throw new RenamingFailureException( M::RENAME_ASSET_FAILURE );
        }
        	
    	return $this;
    }   

    public function searchForAll( $asset_name, $asset_content, $asset_metadata, $search_type )
    {
        return $this->search( T::MATCH_ALL, $asset_name, $asset_content, $asset_metadata, $search_type );
    }
    
    public function searchForAssetContent( $asset_content, $search_type )
    {
        if( trim( $asset_content ) == "" )
        {
            throw new EmptyValueException( M::EMPTY_ASSET_CONTENT );
        }
        return $this->search( T::MATCH_ANY, "", $asset_content, "", $search_type );
    }
    
    public function searchForAssetName( $asset_name, $search_type )
    {
        if( trim( $asset_name ) == "" )
        {
            throw new EmptyNameException( M::EMPTY_ASSET_NAME );
        }
        return $this->search( T::MATCH_ANY, $asset_name, "", "", $search_type );
    }
    
    public function searchForAssetMetadata( $asset_metadata, $search_type )
    {
        if( trim( $asset_metadata ) == "" )
        {
            throw new EmptyValueException( M::EMPTY_ASSET_METADATA );
        }
        return $this->search( T::MATCH_ANY, "", "", $asset_metadata, $search_type );
    }
    
    public function setAccessRights( AccessRightsInformation $ari, $apply_to_children=false )
    {
        if( !BooleanValues::isBoolean( $apply_to_children ) )
            throw new UnacceptableValueException( 
            	"<span style='color:red;font-weight:bold;'>The value $apply_to_children must be a boolean.</span>" );
    
        if( $ari != NULL )
        {
            if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $ari->toStdClass() ); }
        
            $this->service->editAccessRights( $ari->toStdClass(), $apply_to_children ); 
        }
        return $this;
    }
    
    public function setAllLevel( $type, $id_path, $site_name=NULL, $level=T::NONE, $applied_to_children=false )
    {
        $ari = $this->getAccessRights( $type, $id_path, $site_name );
        $ari->setAllLevel( $level );
        $this->setAccessRights( $ari, $applied_to_children );
        return $this;
    }
    
    private function createAsset( stdClass $std, $type, $id_path, $site_name="" )
    {
    	// try retrieval first to avoid creating asset of the same name
		try
		{
			if( $type == Role::TYPE )
			{
				return $this->getRoleByName( $id_path );
			}
			return $this->getAsset( $type, $id_path, $site_name );
		}
		catch( Exception $e )
		{
			$this->service->create( $std );
    	
			if( !$this->service->isSuccessful() )
			{
				echo $this->service->getLastResponse();
				throw new CreationErrorException( M::CREATE_ASSET_FAILURE . $this->service->getMessage() );
			}
			//else echo "Successfully created the asset $type, $id_path, $site_name. " . BR;
		}
		// returns the object created
		if( $type == Role::TYPE )
		{
			return $this->getRoleByName( $id_path );
		}
    	return $this->getAsset( $type, $id_path, $site_name );
    }
    
    private function deleteMessagesWithIds( $ids )
    {
        if( self::DEBUG ) { DebugUtility::out( "Inside deleteMessagesWithIds" ); }
        
        if( !is_array( $ids ) )
            throw new Exception( M::NOT_ARRAY );
            
        if( count( $ids ) > 0 )
        {
            foreach( $ids as $id )
            {
                $this->service->deleteMessage( 
                    $this->service->createIdWithIdType( $id, T::MESSAGE ) );
            }
        }
        
        return $this;
    }
    
    private function getPath( Asset $parent=NULL, $name="" )
    {
        if( $parent == NULL || $parent->getPath() == "/" )
    		$path = $name;
    	else
    		$path = $parent->getPath() . '/' . $name;
    	
		return $path;
    }
    
    private function search( 
        $match_type=T::MATCH_ANY, 
        $asset_name='', 
        $asset_content='', 
        $asset_metadata='', // metadata overrides others when any
        $search_type='' )
    {
        if( !SearchTypes::isSearchType( trim( $search_type ) ) )
        {
            throw new NoSuchTypeException( 
            	"<span style='color:red;font-weight:bold;'>The search type $search_type does not exist.</span>" );
        }
        
        if( $match_type != T::MATCH_ANY && $match_type != T::MATCH_ALL )
        {
            throw new NoSuchTypeException( 
            "<span style='color:red;font-weight:bold;'>The match type $match_type does not exist.</span>" );
        }
    
        $search_for = new stdClass();
        $search_for->matchType     = $match_type;
        $search_for->$search_type  = true;
        
        if( trim( $asset_name ) != "" )
            $search_for->assetName = $asset_name;
        if( trim( $asset_content ) != "" )
            $search_for->assetContent = $asset_content;
        if( trim( $asset_metadata ) != "" )
            $search_for->assetMetadata = $asset_metadata;
            
        if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $search_for ); }
            
        $this->service->search( $search_for );
    
        // if succeeded
        if ( $this->service->isSuccessful() )
        {
            $results = array();
            
            if( !is_null( $this->service->getSearchMatches()->match ) )
            {
                $temp = $this->service->getSearchMatches()->match;
                
                if( !is_array( $temp ) )
                {
                    $temp = array( $temp );
                }
                    
                foreach( $temp as $match )
                {
                    $results[] = new Identifier( $match );
                }
            }
            return $results;
        }
        else
        {
            throw new SearchException( $this->service->getMessage() );
        }
    }
    
    private $service;
    private $sites;
    private $name_site_map;
    private $groups;
    private $roles;
    private $role_name_object_map;
    private $role_id_object_map;
    private $users;
}
?>