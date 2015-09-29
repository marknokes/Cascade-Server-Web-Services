<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/28/2015 Added namespaces.
  * 6/10/2014 Added a dummy string to parentFolderId in edit.
 */
namespace cascade_ws_asset;

use cascade_ws_constants as c;
use cascade_ws_AOHS as aohs;
use cascade_ws_utility as u;
use cascade_ws_exception as e;
use cascade_ws_property as p;

abstract class Container extends ContainedAsset
{
    const DEBUG = false;

    public function __construct( aohs\AssetOperationHandlerService $service, \stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        if( isset( $this->getProperty()->children ) && isset( $this->getProperty()->children->child ) )
        {
            $this->processChildren();
        }
    }

    public function deleteAllChildren()
    {
        if( count( $this->children ) == 0 )
        {
            return $this;
        }
        
        $service = $this->getService();
        
        foreach( $this->children as $child )
        {
            $child_id              = $child->getId();
            $child_type            = $child->getType();
            $identifier            = $service->createId( $child_type, $child_id );
            $operation             = new \stdClass();
            $delete_op             = new \stdClass();
            $delete_op->identifier = $identifier;
            $operation->delete     = $delete_op;
            $operations[]          = $operation;
        }
        
        $service->batch( $operations );
        $this->children               = array();
        $this->container_children_ids = array();
        
        return $this;
    }

    public function edit()
    {
        $asset = new \stdClass();
        $property = $this->getProperty();
        
        if( $property->path == "/" )
        {
            if( $this->getType() == Folder::TYPE ) // type is NOT in property
                $property->parentFolderId = 'some dummy string';
            else
                $property->parentContainerId = 'some dummy string';
        }
        
        $asset->{ $p = $this->getPropertyName() } = $property;

        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
            throw new e\EditingFailureException( 
                c\M::EDIT_ASSET_FAILURE . $service->getMessage() );
        }
        return $this->reloadProperty();
    }
    
    public function getAssetTree()
    {
        return new AssetTree( $this );
    }
    
    public function getChildren()
    {
        return $this->children;
    }

    public function getContainerChildrenIds()
    {
        return $this->container_children_ids;
    }
            
    public function toChild()
    {
        $child_std       = new \stdClass();
        $child_std->id   = $this->getId();
        $child_std->type = $this->getType();
        
        $asset_path_std            = new \stdClass();
        $asset_path_std->path      = $this->getPath();
        $asset_path_std->siteId    = $this->getSiteId();
        $asset_path_std->siteName  = $this->getSiteName();
        
        $child_std->path     = $asset_path_std;
        $child_std->recycled = false;
        $child               = new p\Child( $child_std );
        return $child;
    }
    
    private function processChildren()
    {
        $this->children                = array();
        $this->container_children_ids  = array();

        $children = $this->getProperty()->children->child;
        
        if( !is_array( $children ) )
        {
            $children = array( $children );
        }
        
        foreach( $children as $child )
        {
            $this->children[] = new p\Child( $child );
            
            if( $child->type == $this->getType() )
            {
                $this->container_children_ids[] = $child->id;
            }
        }
    }
    
    private $children;
    private $container_children_ids;
}
?>