<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 9/29/2014 Added expiration folder-related methods.
  * 7/15/2014 Added getMetadataStdClass, setMetadata.
  * 7/1/2014 Removed copy.
 */
abstract class Block extends ContainedAsset
{
    const DEBUG = false;

    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        $this->processMetadata();
    }

    public function edit()
    {
        $asset                           = new stdClass();
        //$this->getProperty()->metadata   = $this->metadata->toStdClass();
		
        $asset->{ $p = $this->getPropertyName() }           = $this->getProperty();
        $asset->{ $p = $this->getPropertyName() }->metadata = $this->metadata->toStdClass();

		if( self::DEBUG ){ DebugUtility::dump( $asset ); }

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
    
    public static function getBlock( $service, $id_string )
    {
    	return self::getAsset( $service, 
    		self::getBlockType( $service, $id_string ), $id_string );
	}

    public static function getBlockType( $service, $id_string )
    {
    	$types      
    	    = array( DataBlock::TYPE, FeedBlock::TYPE, IndexBlock::TYPE, TextBlock::TYPE, XmlBlock::TYPE );
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
        $this->metadata = new Metadata( 
            $this->getProperty()->metadata, 
            $this->getService(), 
            $this->getProperty()->metadataSetId );
    }    

    private $block;          // the property of asset
    private $metadata;
}
?>
