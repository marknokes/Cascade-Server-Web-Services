<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 7/30/2014 Added setAsset.
 */
class Reference extends ContainedAsset
{
    const DEBUG = false;
    const TYPE  = T::REFERENCE;
    
    public function getCreatedBy()
    {
        return $this->getProperty()->createdBy;
    }
    
    public function getCreatedDate()
    {
        return $this->getProperty()->createdDate;
    }
    
    public function getLastModifiedBy()
    {
        return $this->getProperty()->lastModifiedBy;
    }
    
    public function getLastModifiedDate()
    {
        return $this->getProperty()->lastModifiedDate;
    }
    
    public function getReferencedAsset()
    {
        return Asset::getAsset( 
            $this->getService(),
            $this->getProperty()->referencedAssetType,
            $this->getProperty()->referencedAssetId );
    }
    
    public function getReferencedAssetId()
    {
        return $this->getProperty()->referencedAssetId;
    }
    
    public function getReferencedAssetPath()
    {
        return $this->getProperty()->referencedAssetPath;
    }
    
    public function getReferencedAssetType()
    {
        return $this->getProperty()->referencedAssetType;
    }
    
    public function setAsset( Asset $asset )
    {
    	$property = $this->getProperty();
    	$property->referencedAssetId   = $asset->getId();
    	$property->referencedAssetPath = $asset->getPath();
    	$property->referencedAssetType = $asset->getType();
    	
    	$asset                          = new stdClass();
        $asset->{ $p = $this->getPropertyName() } = $property;
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
}
?>