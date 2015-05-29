<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 9/29/2014 Added expiration folder-related methods.
  * 8/25/2014 Overrode edit.
  * 7/22/2014 Added isPublishable.
  * 7/14/2014 Added getMetadataStdClass, setMetadata.
  * 7/1/2014 Removed copy.
 */
class Folder extends Container
{
    const DEBUG = false;
    const DUMP  = false;
    const TYPE  = T::FOLDER;
    
    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        $this->processMetadata();
    }
    
    // Adds a workflow definition to the settings
    public function addWorkflow( WorkflowDefinition $wf )
    {
        $this->getWorkflowSettings()->addWorkflowDefinition( $wf->getIdentifier() );
        return $this;
    }
    
    public function edit()
    {
        $asset  = new stdClass();
        $folder = $this->getProperty();
        
        if( $folder->path == "/" )
        {
            $folder->parentFolderId = 'some dummy string';
        }
        $folder->metadata = $this->getMetadata()->toStdClass();
        
        $asset->{ $p = $this->getPropertyName() } = $folder;

        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
            throw new EditingFailureException( 
                M::EDIT_ASSET_FAILURE . 
                "<span style='color:red;font-weight:bold;'>Path: " . $folder->path . "</span>" .
                $service->getMessage() );
        }
        return $this->reloadProperty();
    }
    
    public function editWorkflowSettings( 
        $apply_inherit_workflows_to_children, $apply_require_workflow_to_children )
    {
        if( !BooleanValues::isBoolean( $apply_inherit_workflows_to_children ) )
            throw new UnacceptableValueException( 
                "The value $apply_inherit_workflows_to_children must be a boolean." );
                
        if( !BooleanValues::isBoolean( $apply_require_workflow_to_children ) )
            throw new UnacceptableValueException( 
                "The value $apply_require_workflow_to_children must be a boolean." );
    
        $service = $this->getService();
        $service->editWorkflowSettings( $this->workflow_settings->toStdClass(),
            $apply_inherit_workflows_to_children, $apply_require_workflow_to_children );
            
        if( !$service->isSuccessful() )
        {
            throw new EditingFailureException( 
                M::EDIT_WORKFLOW_SETTINGS_FAILURE . $service->getMessage() );
        }
        return $this;
    }
    
    public function getCreatedBy()
    {
        return $this->getProperty()->createdBy;
    }
    
    public function getCreatedDate()
    {
        return $this->getProperty()->createdDate;
    }
    
    public function getDynamicField( $name )
    {
        return $this->metadata->getDynamicField( $name );
    }
    
    public function getDynamicFields()
    {
        return $this->metadata->getDynamicFields();
    }
    
    public function getExpirationFolderId()
    {
        return $this->getProperty()->expirationFolderId;
    }
    
    public function getExpirationFolderPath()
    {
        return $this->getProperty()->expirationFolderPath;
    }
    
    public function getExpirationFolderRecycled()
    {
        return $this->getProperty()->expirationFolderRecycled;
    }
    
    public function getFolderChildrenIds()
    {
        return $this->getContainerChildrenIds();
    }

    public function getLastModifiedBy()
    {
        return $this->getProperty()->lastModifiedBy;
    }
    
    public function getLastModifiedDate()
    {
        return $this->getProperty()->lastModifiedDate;
    }
    
    public function getLastPublishedBy()
    {
        return $this->getProperty()->lastPublishedBy;
    }
    
    public function getLastPublishedDate()
    {
        return $this->getProperty()->lastPublishedDate;
    }
    
    public function getMetadata()
    {
        return $this->metadata;
    }
    
    public function getMetadataSet()
    {
        $service = $this->getService();
        //echo $this->metadataSetId . BR;
        
        return new MetadataSet( 
            $service, 
            $service->createId( MetadataSet::TYPE, 
                $this->getProperty()->metadataSetId ) );
    }
    
    public function getMetadataSetId()
    {
        return $this->getProperty()->metadataSetId;
    }
    
    public function getMetadataSetPath()
    {
        return $this->getProperty()->metadataSetPath;
    }
    
    public function getMetadataStdClass()
    {
        return $this->metadata->toStdClass();
    }
    
    public function getParentFolderId()
    {
        return $this->getParentContainerId();
    }
    
    public function getParentFolderPath()
    {
        return $this->getParentContainerPath();
    }

    public function getShouldBeIndexed()
    {
        return $this->getProperty()->shouldBeIndexed;
    }
    
    public function getShouldBePublished()
    {
        return $this->getProperty()->shouldBePublished;
    }

    public function getWorkflowSettings()
    {
        if( $this->workflow_settings == NULL )
        {
            $service = $this->getService();
        
            $service->readWorkflowSettings( 
                $service->createId( self::TYPE, $this->getProperty()->id ) );
    
            if( $service->isSuccessful() )
            {
                //echo S_PRE;
                //var_dump( $service->getReply()->readWorkflowSettingsReturn->workflowSettings );
                //echo E_PRE;
                $this->workflow_settings = new WorkflowSettings( 
                    $service->getReply()->readWorkflowSettingsReturn->workflowSettings );
            }
            else
            {
                throw new Exception( $service->getMessage() );
            }
        }
        return $this->workflow_settings;
    }
    
    public function hasDynamicField( $name )
    {
        return $this->metadata->hasDynamicField( $name );
    }
    
    public function isPublishable()
    {
    	$path = $this->getPath();
    	if( self::DEBUG ) { DebugUtility::out( $path ); }
    	
    	if( $this->getPath() == '/' )
    	{
    		return $this->getShouldBePublished();
    	}
    	else
    	{
    		
    		$parent = $this->getAsset( $this->getService(), Folder::TYPE, $this->getParentContainerId() );
    		return $parent->isPublishable() && $this->getShouldBePublished();
    	}
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
                $service->createId( self::TYPE, $this->getProperty()->id ), $destination_std );
        }
        return $this;
    }
    
    public function setExpirationFolder( Folder $f )
    {
    	$this->getProperty()->expirationFolderId   = $f->getId();
    	$this->getProperty()->expirationFolderPath = $f->getPath();
    	return $this;
    }
    
    public function setMetadata( stdClass $m )
    {
    	$this->getProperty()->metadata = $m;
    	$this->edit();
    }
    
    public function setMetadataSet( MetadataSet $ms )
    {
        if( $ms == NULL )
        {
            throw new NullAssetException( M::NULL_ASSET );
        }
    
        $this->getProperty()->metadataSetId   = $ms->getId();
        $this->getProperty()->metadataSetPath = $ms->getPath();
        $this->edit();
        $this->processMetadata();
        
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

    private function processMetadata()
    {
        $this->metadata = new Metadata( 
            $this->getProperty()->metadata, 
            $this->getService(), 
            $this->getProperty()->metadataSetId );
    }

    private $metadata;
    private $children;
    private $workflow_settings;
}
?>
