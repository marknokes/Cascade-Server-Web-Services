<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/28/2015 Added namespaces.
  * 8/1/2014 Added toStdClass.
 */
namespace cascade_ws_asset;

use cascade_ws_constants as c;
use cascade_ws_AOHS as aohs;
use cascade_ws_utility as u;
use cascade_ws_exception as e;
use cascade_ws_property as p;

class Audit
{
    const DEBUG = false;

    public function __construct( 
        aohs\AssetOperationHandlerService $service, \stdClass $audit_std )
    {
        if( $service == NULL )
        {
            throw new e\NullServiceException(
            	S_SPAN . c\M::NULL_SERVICE . E_SPAN );
        }
        
        if( $audit_std == NULL )
        {
            throw new e\EmptyValueException(
            	S_SPAN . c\M::EMPTY_AUDIT . E_SPAN );
        }
        
        if( self::DEBUG ) { u\DebugUtility::dump( $audit_std->identifier ); }
        
        $this->service    = $service;
        $this->audit_std  = $audit_std;
        $this->user       = $audit_std->user;
        $this->action     = $audit_std->action;
        $this->identifier = new p\Identifier( $audit_std->identifier );
        $this->date_time  = new \DateTime( $audit_std->date );
    }
    
    public function display()
    {
        echo c\L::USER       . $this->user . BR .
             c\L::ACTION     . $this->action . BR .
             c\L::ID         . $this->identifier->getId() . BR .
             c\L::ASSET_TYPE . $this->identifier->getType() . BR .
             c\L::DATE       . date_format( $this->date_time, 'Y-m-d H:i:s' ) . BR . HR;
        
        return $this;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function getAuditedAsset()
    {
        return $this->identifier->getAsset();
    }
    
    public function getDate()
    {
        return $this->date_time;
    }
    
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    public function getUser()
    {
        return Asset::getAsset( $service, User::TYPE, $this->user );
    }
    
    public function getUserString()
    {
    	return $this->user;
    }
    
    public function toStdClass()
    {
    	return $this->audit_std;
    }
    
    /* for sorting, ascending */
    public static function compare( Audit $a1, Audit $a2 )
    {
        if( $a1->getDate() == $a2->getDate() )
        {
            return 0;
        }
        else if( $a1->getDate() < $a2->getDate() )
        {
            return -1;
        }
        else
        {
            return 1;
        }
    }
    
    public static function compareAscending( Audit $a1, Audit $a2 )
    {
		return self::compare( $a1, $a2 );
    }

    public static function compareDescending( Audit $a1, Audit $a2 )
    {
        if( $a1->getDate() == $a2->getDate() )
        {
            return 0;
        }
        else if( $a1->getDate() < $a2->getDate() )
        {
            return 1;
        }
        else
        {
            return -1;
        }
    }

    private $service;
    private $user;
    private $action;
    private $identifier;
    private $date_time;
    private $audit_std;
}
?>