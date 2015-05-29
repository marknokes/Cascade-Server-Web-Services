<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 7/1/2014 Removed copy.
 */
abstract class Linkable extends ContainedAsset
{
    const DEBUG = false;

    public function __construct( AssetOperationHandlerService $service, 
        stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        // Skip page for content type to be set
        if( $this->getType() == File::TYPE || $this->getType() == Symlink::TYPE )
        {
            $this->processMetadata();
        }
    }

    public function edit()
    {
        $asset                          = new stdClass();
        $this->getProperty()->metadata  = $this->metadata->toStdClass();
        $asset->{ $p = $this->getPropertyName() } = $this->getProperty();
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
    
    public function getLastModifiedBy()
    {
        return $this->getProperty()->lastModifiedBy;
    }
    
    public function getLastModifiedDate()
    {
        return $this->getProperty()->lastModifiedDate;
    }
    
    public function getMetadata() 
    {
        return $this->metadata;
    }
    
    public function getMetadataSet()
    {
        $service = $this->getService();
        
        return new MetadataSet(
            $service, 
            $service->createId( MetadataSet::TYPE, 
                $this->getMetadataSetId() ) );
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
    
    public function hasDynamicField( $name )
    {
        return $this->metadata->hasDynamicField( $name );
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

    public function setMetadataSet( MetadataSet $m )
    {
        if( $m == NULL )
        {
            throw new NullAssetException( M::NULL_ASSET );
        }
    
        $this->getProperty()->metadataSetId   = $m->getId();
        $this->getProperty()->metadataSetPath = $m->getPath();
        $this->edit();
        $this->processMetadata();

        return $this;
    }
    
    public function setPageContentType( ContentType $c )
    {
        if( $this->getType() != Page::TYPE )
        {
            throw new WrongAssetTypeException( );
        }
        if( $c == NULL )
        {
            throw new NullAssetException( NULL_ASSET );
        }
        $this->page_content_type = $c;
        $this->processMetadata();
        return $this;
    }
    
    public static function getLinkable( $service, $id_string )
    {
    	return self::getAsset( $service, 
    		self::getLinkableType( $service, $id_string ), $id_string );
	}

    public static function getLinkableType( $service, $id_string )
    {
    	$types      
    	    = array( Page::TYPE, File::TYPE, Symlink::TYPE );
        $type_count = count( $types );
        
        for( $i = 0; $i < $type_count; $i++ )
        {
            $id = $service->createId( $types[ $i ], $id_string );
            $operation = new stdClass();
            $read_op   = new stdClass();
    
            $read_op->identifier = $id;
            $operation->read     = $read_op;
            $operations[]        = $operation;
        }
        
        $service->batch( $operations );
        
        $reply_array = $service->getReply()->batchReturn;
        
        for( $j = 0; $j < $type_count; $j++ )
        {
            if( $reply_array[ $j ]->readResult->success == 'true' )
            {
                foreach( T::$type_property_name_map as $type => $property )
                {
                    if( $reply_array[ $j ]->readResult->asset->$property != NULL )
                        return $type;
                }
            }
        }
        
        return "The id does not match any asset type.";
    }

    private function processMetadata()
    {
        if( $this->getType() == Page::TYPE && $this->page_content_type != NULL )
        {
            $metadata_set_id = $this->page_content_type->getMetadataSetId();
        }
        else
        {
            $metadata_set_id = $this->getProperty()->metadataSetId;
        }
        
        $this->metadata = new Metadata( 
            $this->getProperty()->metadata, 
            $this->getService(), $metadata_set_id
        );
    }

    private $metadata;
    private $page_content_type;
}
?>