<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/28/2015 Added namespaces.
  * 9/9/2014 Added exception in copy.
  * 8/14/2014 Started using style in error messages.
  * 8/1/2014 Added getAudits, but unable to test with user, group, role
  * 7/1/2014 Added copy.
 */

/**
 * Abstract Asset class, inherited by all classes representing assets
 *
 * @link http://www.upstate.edu/cascade-admin/projects/web-services/oop/classes/asset-classes/asset.php
 */
namespace cascade_ws_asset;

use cascade_ws_constants as c;
use cascade_ws_AOHS as aohs;
use cascade_ws_utility as u;
use cascade_ws_exception as e;
use cascade_ws_property as p;

abstract class Asset
{
    const DEBUG      = false;
    const DUMP       = false;
    const NAME_SPACE = "cascade_ws_asset";
    
    /**
     * Construct an asset object
     *
     * @param AssetOperationHandlerService $service    The service object
     * @param stdClass                     $identifier The identifier of the asset
     * @throws NullServiceException        if the service object is NULL
     * @throws NullIdentifierException     if the identifier object is NULL
     * @throws NullAssetException          if the asset cannot be retrieved
     */
    public function __construct( aohs\AssetOperationHandlerService $service, \stdClass $identifier )
    {
        if( $service == NULL )
            throw new e\NullServiceException( c\M::NULL_SERVICE );
            
        if( $identifier == NULL )
            throw new e\NullIdentifierException( c\M::NULL_IDENTIFIER );
        
        if( self::DEBUG && self::DUMP ) { u\DebugUtility::dump( $identifier ); }
        
        // get the property
        $property = $service->retrieve( 
            $identifier, c\T::$type_property_name_map[ $identifier->type ] );
            
        if( $property == NULL )
        {
            if( isset( $identifier->id ) )
        	    $id = $identifier->id;

            if( isset( $identifier->path ) )
            {
        	    $path      = $identifier->path->path;
        	    $site_name = $identifier->path->siteName;
            }
        	
        	if( !isset( $id ) )
        		$id = $path;
        	
            throw new e\NullAssetException(
                S_SPAN . "The " . 
                c\T::$type_property_name_map[ $identifier->type ] . 
                " cannot be retrieved. ID/Path: " . $id . ". " .
                ( isset( $site_name ) ? "Site: " . $site_name . ". "  : "" ) . E_SPAN . BR .
                $service->getMessage() );
        }
            
        // store information
        $this->service       = $service;
        $this->identifier    = $identifier; //stdClass
        $this->type          = $identifier->type;
        $this->property_name = c\T::$type_property_name_map[ $this->type ];
        $this->property      = $property;
        if( isset( $property->id ) )
            $this->id            = $property->id;
        if( isset( $property->name ) )
            $this->name          = $property->name;
        if( isset( $property->path ) )
            $this->path          = $property->path;
        if( isset( $property->siteId ) )
            $this->site_id       = $property->siteId;
        if( isset( $property->siteName ) )
            $this->site_name     = $property->siteName;
    }
    
    public function copy( Container $parent, $new_name )
    {
    	if( $new_name == "" )
    	{
    		throw new e\EmptyNameException( c\M::EMPTY_NAME );
    	}
    	
        $service         = $this->getService();
        $self_identifier = $service->createId( $this->getType(), $this->getId() );
        
        $service->copy( $self_identifier, $parent->getIdentifier(), $new_name, false );
        
        if( $service->isSuccessful() )
        {
        	$parent->reloadProperty(); // get info of new child
			$parent      = $parent->getProperty();
			$children    = $parent->children->child;
			$child_count = count( $children );
			
			if( $child_count == 1 )
			{
				$children = array( $children );
			}
			
            // look for the new child
            foreach( $children as $child )
            {
                $child_path = $child->path->path;
                $child_path_array = explode( '/', $child_path );
                
                if( in_array( $new_name, $child_path_array ) )
                {
                    $child_found = $child;
                    break;
                }
            }
            // get the digital id of child
            $child_id = $child_found->id;
            
            // return new block object
            return Asset::getAsset( $service, $this->getType(), $child_id );
        }
        else
        {
            throw new e\CopyErrorException( c\M::COPY_ASSET_FAILURE . $service->getMessage() );
        }
    }
    
    public function display()
    {
        echo S_H2 . "A::display" . E_H2 .
             c\L::ID .            $this->id .            BR .
             c\L::NAME .          $this->name .          BR .
             c\L::PATH .          $this->path .          BR .
             c\L::SITE_ID .       $this->site_id .       BR .
             c\L::SITE_NAME .     $this->site_name .     BR .
             c\L::PROPERTY_NAME . $this->property_name . BR .
             c\L::TYPE .          $this->type .          BR .
             HR;
        return $this;
    }

    public function dump( $formatted=false )
    {
        if( $formatted ) echo S_H2 . c\L::READ_DUMP . E_H2 . S_PRE;
        var_dump( $this->property );
        if( $formatted ) echo E_PRE . HR;
        
        return $this;
    }
    
    public function edit()
    {
        return $this->reloadProperty();
    }
    
    public function getAudits( $type="", \DateTime $start_time=NULL, \DateTime $end_time=NULL )
    {
        if( !is_string( $type ) || !c\AuditTypes::isAuditType( $type ) )
        {
            if( self::DEBUG && !is_string( $type ) ) { u\DebugUtility::out( "Not a string" ); }
            throw new e\NoSuchTypeException( c\M::WRONG_AUDIT_TYPE );
        }

        $start = false;
        $end   = false;
        
        if( $start_time != NULL )
        {
            if( $end_time != NULL )
            {
                if( $end_time < $start_time )
                    throw new \Exception( c\M::SMALLER_END_TIME );
                    
                $end = true;
            }
            $start = true;
        }

        $a_std = new \stdClass();
        
        // unable to test with user, group, role
        if( $this->getType() == User::TYPE )
        {
        	$a_std->username = $this->getName();
        }
        else if( $this->getType() == Group::TYPE )
        {
        	$a_std->groupname = $this->getName();
        }
        else if( $this->getType() == Role::TYPE )
        {
        	$a_std->rolename = $this->getName();
        }
        else
        {
        	$a_std->identifier->id   = $this->getId();
        	$a_std->identifier->type = $this->getType();
        }
        
        if( $type != "" )
            $a_std->auditType  = $type;
            
        $service = $this->getService();
        $service->readAudits( $a_std );
        $audits  = array();
        
        if( $service->isSuccessful() )
        {
            if( self::DEBUG ) { u\DebugUtility::dump( $service->getAudits() ); }
        
            $audit_stds = $service->getAudits()->audit;
            
            if( $audit_stds != NULL && !is_array( $audit_stds ) )
            {
                $audit_stds = array( $audit_stds );
            }
            
            $count = count( $audit_stds );
            
            if( $count > 0 )
            {
				foreach( $audit_stds as $audit_std )
				{
					if( self::DEBUG && self::DUMP ) { u\DebugUtility::dump( $audit_std ); }
		
					$audit = new Audit( $service, $audit_std );
			
					if( $start && $audit->getDate() >= $start_time )
					{
						if( $end && $audit->getDate() <= $end_time )
						{
							$audits[] = $audit;
						}
						else if( !$end )
						{
							$audits[] = $audit;
						}
					}
					else if( !$start )
					{
						if( $end && $audit->getDate() <= $end_time )
						{
							$audits[] = $audit;
						}
						else if( !$end )
						{
							$audits[] = $audit;
						}
					}
				}
				usort( $audits, self::NAME_SPACE . "\\" . 'Audit::compare' );
            }
        }
        else
        {
            echo $service->getMessage();
        }

        return $audits;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getProperty()
    {
        return $this->property;
    }
    
    public function getPropertyName()
    {
        if( self::DEBUG ) { u\DebugUtility::out( "From Asset::getPropertyName " . $this->property_name ); }
        return $this->property_name;
    }
    
    public function getService()
    {
        return $this->service;
    }
    
    public function getSiteId()
    {
        return $this->site_id;
    }
  
    public function getSiteName()
    {
        return $this->site_name;
    }
    
    public function getSubscribers()
    {
        $this->service->listSubscribers( $this->identifier );
            
        if( $this->service->isSuccessful() )
        {
            if( self::DEBUG ) { u\DebugUtility::out( "Successfully listing subscribers" ); }
            $results = array();
            
            // there are subscribers
            if isset( $this->service->getReply()->listSubscribersReturn->subscribers->assetIdentifier )
            {
                $subscribers = 
                    $this->service->getReply()->listSubscribersReturn->subscribers->assetIdentifier;
                
                if( !is_array( $subscribers ) )
                    $subscribers = array( $subscribers );
                    
                foreach( $subscribers as $subscriber )
                {
                    $identifier = new p\Identifier( $subscriber );
                    $results[] = $identifier;
                }
            }
            return $results;
        }
        else
        {
            echo $this->service->getMessage();
        }
        return NULL;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function publishSubscribers( Destination $destination=NULL )
    {
        $subscriber_ids = $this->getSubscribers();
        
        if( $destination != NULL )
        {
            $destination_std           = new \stdClass();
            $destination_std->id       = $destination->getId();
            $destination_std->type     = $destination->getType();
        }
        
        if( $subscriber_ids != NULL )
        {
            foreach( $subscriber_ids as $subscriber_id )
            {
                if( self::DEBUG ) { u\DebugUtility::out( "Publishing " . $subscriber_id->getId() ); }
                $this->getService()->publish( $subscriber_id->toStdClass(), $destination_std );
            }
        }
        return $this;
    }
    
    public function reloadProperty()
    {
        $this->property = 
            $this->service->retrieve( $this->identifier, $this->property_name );
        return $this;
    }
    
    public static function getAsset( $service, $type, $id_path, $site_name=NULL )
    {
        if( !in_array( $type, c\T::getTypeArray() ) )
            throw new e\NoSuchTypeException( "The type $type does not exist." );
            
        $class_name = c\T::$type_class_name_map[ $type ]; // get class name
        
        try
        {
        	$class_name = self::NAME_SPACE . "\\" . $class_name;
        	
        	return new $class_name( // call constructor
            	$service, 
            	$service->createId( $type, $id_path, $site_name ) );
        }
        catch( \Exception $e )
        {
        	if( self::DEBUG ) { u\DebugUtility::out( $e->getMessage() ); }
        	throw $e;
        }
    }
    
    /** @var AssetOperationHandlerService The service object */
    private $service;
    /** @var stdClass The identifier object */
    private $identifier;
    /** @var string The type */
    private $type;
    /** @var string The property name */
    private $property_name;
    /** @var stdClass The property */
    private $property;
    /** @var string The 32-digit id */
    private $id;
    /** @var string The name */
    private $name;
    /** @var string The path */
    private $path;
    /** @var string The 32-digit site id */
    private $site_id;
    /** @var string The site name */
    private $site_name;
}
?>