<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 8/1/2014 Added toStdClass.
 */
class Audit
{
    const DEBUG = false;

    public function __construct( 
        AssetOperationHandlerService $service, stdClass $audit_std )
    {
        if( $service == NULL )
        {
            throw new NullServiceException( M::NULL_SERVICE );
        }
        
        if( $audit_std == NULL )
        {
            throw new EmptyValueException( EMPTY_AUDIT );
        }
        
        if( self::DEBUG ) { DebugUtility::dump( $audit_std->identifier ); }
        
        $this->service    = $service;
        $this->audit_std  = $audit_std;
        $this->user       = $audit_std->user;
        $this->action     = $audit_std->action;
        $this->identifier = new Identifier( $audit_std->identifier );
        $this->date_time  = new DateTime( $audit_std->date );
    }
    
    public function display()
    {
        echo L::USER       . $this->user . BR .
             L::ACTION     . $this->action . BR .
             L::ID         . $this->identifier->getId() . BR .
             L::ASSET_TYPE . $this->identifier->getType() . BR .
             L::DATE       . date_format( $this->date_time, 'Y-m-d H:i:s' ) . BR . HR;
        
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