<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/28/2015 Added namespaces.
  * 10/1/2014 Added getWorkflow.
  * 7/22/2014 Added isPublishable.
  * 7/17/2014 Fixed a bug in setText.
 */
namespace cascade_ws_asset;

use cascade_ws_constants as c;
use cascade_ws_AOHS as aohs;
use cascade_ws_utility as u;
use cascade_ws_exception as e;
use cascade_ws_property as p;

class File extends Linkable
{
    const DEBUG = false;
    const TYPE  = c\T::FILE;
    
    public function getData()
    {
        return $this->getProperty()->data;
    }
    
    public function getLastPublishedBy()
    {
        return $this->getProperty()->lastPublishedBy;
    }
    
    public function getLastPublishedDate()
    {
        return $this->getProperty()->lastPublishedDate;
    }
    
    public function getMaintainAbsoluteLinks()
    {
        return $this->getProperty()->maintainAbsoluteLinks;
    }
    
    public function getRewriteLinks()
    {
        return $this->getProperty()->rewriteLinks;
    }
    
    public function getShouldBeIndexed()
    {
        return $this->getProperty()->shouldBeIndexed;
    }
    
    public function getShouldBePublished()
    {
        return $this->getProperty()->shouldBePublished;
    }
    
    public function getText()
    {
        return $this->getProperty()->text;
    }
    
    public function getWorkflow()
    {
        $service = $this->getService();
        $service->readWorkflowInformation( $service->createId( self::TYPE, $this->getProperty()->id ) );
        
        if( $service->isSuccessful() )
        {
            if( $service->getReply()->readWorkflowInformationReturn->workflow != NULL )
                return new p\Workflow( 
                	$service->getReply()->readWorkflowInformationReturn->workflow, $service );
            else
                return NULL; // no workflow
        }
        else
        {
            throw new e\NullAssetException( 
            	S_SPAN . c\M::READ_WORKFLOW_FAILURE . E_SPAN );
        }
    }
    
    public function isPublishable()
    {
    	$parent = $this->getAsset( $this->getService(), Folder::TYPE, $this->getParentContainerId() );
    	return $parent->isPublishable() && $this->getShouldBePublished();
    }
    
    public function publish( Destination $destination=NULL )
    {
        if( $destination != NULL )
        {
            $destination_std           = new \stdClass();
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
    
    public function setData( $data )
    {
        $this->getProperty()->data = $data;
        return $this;
    }
    
    public function setMaintainAbsoluteLinks( $bool )
    {
        if( !c\BooleanValues::isBoolean( $bool ) )
            throw new e\UnacceptableValueException( 
            	S_SPAN . "The value $bool must be a boolean." . E_SPAN );
        
        $this->getProperty()->maintainAbsoluteLinks = $bool;
        
        return $this;
    }
    
    public function setRewriteLinks( $bool )
    {
        if( !c\BooleanValues::isBoolean( $bool ) )
            throw new e\UnacceptableValueException( 
            	S_SPAN . "The value $bool must be a boolean." . E_SPAN );
        
        $this->getProperty()->rewriteLinks = $bool;
        
        return $this;
    }
    
    public function setShouldBeIndexed( $bool )
    {
        if( !c\BooleanValues::isBoolean( $bool ) )
            throw new e\UnacceptableValueException( 
            	S_SPAN . "The value $bool must be a boolean." . E_SPAN );
            
        $this->getProperty()->shouldBeIndexed = $bool;
        return $this;
    }
    
    public function setShouldBePublished( $bool )
    {
        if( !c\BooleanValues::isBoolean( $bool ) )
            throw new e\UnacceptableValueException( 
            	S_SPAN . "The value $bool must be a boolean." . E_SPAN );
            
        $this->getProperty()->shouldBePublished = $bool;
        return $this;
    }
    
    public function setText( $text )
    {
        $this->getProperty()->text = $text;
        return $this;
    }
}
?>