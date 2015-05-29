<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
abstract class ContainedAsset extends Asset
{
    const DEBUG = false;

    public function getParentContainer()
    {
        if( $this->getType() == T::SITE )
        {
            throw new WrongAssetTypeException( M::SITE_NO_PARENT_CONTAINER );
        }
        
        if( $this->getParentContainerId() != NULL )
        {
            $parent_id    = $this->getParentContainerId();
            $parent_type  = T::$type_parent_type_map[ $this->getType() ];
            
            return Asset::getAsset( $this->getService(), $parent_type, $parent_id );
        }
        return NULL;
    }
    
    public function getParentContainerId()
    {
        if( $this->getType() == T::SITE )
        {
            throw new WrongAssetTypeException( M::SITE_NO_PARENT_CONTAINER );
        }
        
        if( $this->getProperty()->parentFolderId != NULL )
            return $this->getProperty()->parentFolderId;
        else
            return $this->getProperty()->parentContainerId;
    }
    
    public function getParentContainerPath()
    {
        if( $this->getType() == T::SITE )
        {
            throw new WrongAssetTypeException( M::SITE_NO_PARENT_CONTAINER );
        }
        
        if( $this->getProperty()->parentFolderPath != NULL )
            return $this->getProperty()->parentFolderPath;
        else
            return $this->getProperty()->parentContainerPath;
    }
    
    public function isInContainer( Container $c )
    {
        if( $this->getType() == T::SITE )
        {
            throw new WrongAssetTypeException( M::SITE_NO_PARENT_CONTAINER );
        }
        return $c->getId() == $this->getParentContainerId();
    }
    
    public function move( Container $new_parent, $doWorkflow=false )
    {
        if( $this->getType() == T::SITE )
        {
            throw new WrongAssetTypeException( M::SITE_NO_PARENT_CONTAINER );
        }
        
        if( $new_parent == NULL )
        {
            throw new NullAssetException( M::NULL_CONTAINER );
        }
        $this->moveRename( $new_parent, NULL, $doWorkflow );
        $this->reloadProperty();
        
        return $this;
    }
    
    public function rename( $new_name, $doWorkflow=false )
    {
        if( $this->getType() == T::SITE )
        {
            throw new WrongAssetTypeException( M::SITE_NO_PARENT_CONTAINER );
        }

        if( trim( $new_name ) == "" )
        {
            throw new EmptyValueException( M::EMPTY_NAME );
        }
        $this->moveRename( NULL, $new_name, $doWorkflow );
        $this->reloadProperty();
        
        return $this;
    }
    
    private function moveRename( $parent_container, $new_name, $doWorkflow=false )
    {
        if( !BooleanValues::isBoolean( $doWorkflow ) )
            throw new UnacceptableValueException( "The value $doWorkflow must be a boolean." );
            
        $parent_id = NULL;
        
        if( $parent_container != NULL )
        {
            $parent_id = $parent_container->getIdentifier();
        }
    
        $identifier = $this->getIdentifier();

        $this->getService()->move( $identifier, $parent_id, $new_name, $doWorkflow );
        
        if( !$this->getService()->isSuccessful() )
        {
            throw new RenamingFailureException( 
                M::RENAME_ASSET_FAILURE . $this->getService()->getMessage() );
        }
    }
}
?>