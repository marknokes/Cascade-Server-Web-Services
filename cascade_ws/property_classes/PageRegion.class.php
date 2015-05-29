<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 7/16/2014 Started using DebugUtility::out and DebugUtility::dump.
 */
class PageRegion extends Property
{
    const DEBUG = false;
    const DUMP  = false;
    
    public function __construct( 
    	stdClass $region=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
    	if( isset( $region ) )
    	{
			$this->id              = $region->id;
			$this->name            = $region->name;
			$this->block_id        = $region->blockId; // NULL
			$this->block_path      = $region->blockPath; // NULL
			$this->block_recycled  = $region->blockRecycled;
			$this->no_block        = $region->noBlock;
			$this->format_id       = $region->formatId; // NULL
			$this->format_path     = $region->formatPath; // NULL
			$this->format_recycled = $region->formatRecycled;
			$this->no_format       = $region->noFormat;
			$this->service         = $service;
		
			if( self::DEBUG ) { DebugUtility::out( "Block ID: " . $this->block_id ); }
		}
    }
    
    public function display()
    {
        echo "ID: " . $this->id . BR .
             "Name: " . $this->name . BR;
        
        return $this;
    }
    
    public function getBlock()
    {
        if( self::DEBUG ) { DebugUtility::out( "Name: " . $this->name . BR . "Block ID: " . $this->block_id );; }
    
        if( $this->block_id != NULL && $this->block_id != "" && $this->service != NULL )
        {
            if( self::DEBUG ) {  DebugUtility::out( "Type of block: " . $this->getType( $this->block_id ) ); }
        
            return Asset::getAsset( 
                $this->service,
                $this->getType( $this->block_id ),
                $this->block_id );
        }
        return NULL;
    }
    
    public function getBlockId()
    {
        return $this->block_id;
    }
    
    public function getBlockPath()
    {
        return $this->block_path;
    }
    
    public function getBlockRecycled()
    {
        return $this->block_recycled;
    }
    
    public function getFormat()
    {
        if( $this->format_id != NULL && $this->format_id != "" && $this->service != NULL )
        {
            if( self::DEBUG ) {  DebugUtility::out( __FUNCTION__ . BR . "Type of format: " . $this->getType( $this->format_id ) . BR . "Format ID: " . $this->format_id ); }
            
            return Asset::getAsset( 
                $this->service,
                $this->getType( $this->format_id ),
                $this->format_id );
        }
        return NULL;
    }
    
    public function getFormatId()
    {
        return $this->format_id;
    }
    
    public function getFormatPath()
    {
        return $this->format_path;
    }
    
    public function getFormatRecycled()
    {
        return $this->format_recycled;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getNoBlock()
    {
        return $this->no_block;
    }
    
    public function getNoFormat()
    {
        return $this->no_format;
    }
    
    public function setBlock( Block $b=NULL, $block_recycled=false, $no_block=false )
    {
        if( !BooleanValues::isBoolean( $block_recycled ) )
            throw new UnacceptableValueException( "The value $block_recycled must be a boolean" );
            
        if( !BooleanValues::isBoolean( $no_block ) )
            throw new UnacceptableValueException( "The value $no_block must be a boolean" );
            
        if( $b != NULL )
        {
            if( strpos( get_class( $b ), 'Block' ) !== false )
            {
                $this->block_id   = $b->getId();
                $this->block_path = $b->getPath();
            }
            else
            {
                throw new NullAssetException( "The block " . $b->getName() . " does not exist" );
            }
            
            $this->block_recycled = $block_recycled;
            $this->no_block       = $no_block;
        }
        else
        {
            $this->block_id   = NULL;
            $this->block_path = NULL;
        }
        return $this;
    }
    
    public function setFormat( Format $f=NULL, $format_recycled=false, $no_format=false )
    {
        if( !BooleanValues::isBoolean( $format_recycled ) )
            throw new UnacceptableValueException( "The value $format_recycled must be a boolean" );
            
        if( !BooleanValues::isBoolean( $no_format ) )
            throw new UnacceptableValueException( "The value $no_format must be a boolean" );
            
        if( $f != NULL )
        {
            if( strpos( get_class( $f ), 'Format' ) !== false )
            {
                $this->format_id   = $f->getId();
                $this->format_path = $f->getPath();
            }
            else
            {
                throw new NullAssetException( "The format " . $f->getName() . " does not exist" );
            }
            
            $this->format_recycled = $format_recycled;
            $this->no_format       = $no_format;
        }
        else
        {
            $this->format_id   = NULL;
            $this->format_path = NULL;
        }
        return $this;
    }
    
    public function setNoBlock( $value )
    {
        if( !BooleanValues::isBoolean( $value ) )
            throw new UnacceptableValueException( "The value $value must be a boolean" );
        $this->no_block = $value;
        return $this;
    }
    
    public function setNoFormat( $value )
    {
        if( !BooleanValues::isBoolean( $value ) )
            throw new UnacceptableValueException( "The value $value must be a boolean" );
            
        $this->no_format = $value;
        return $this;
    }
    
    public function toStdClass()
    {
        $obj                 = new stdClass();
        $obj->id             = $this->id;
        $obj->name           = $this->name;
        $obj->blockId        = $this->block_id;
        $obj->blockPath      = $this->block_path;
        $obj->blockRecycled  = $this->block_recycled;
        $obj->noBlock        = $this->no_block;
        $obj->formatId       = $this->format_id;
        $obj->formatPath     = $this->format_path;
        $obj->formatRecycled = $this->format_recycled;
        $obj->noFormat       = $this->no_format;
        
        return $obj;
    }
    
    private function getType( $id_string )
    {
        if( self::DEBUG) { DebugUtility::out( "string: " . $id_string ); }

        if( $this->service != NULL )
        {
            $types = array( 'block', 'format' );
            $type_count = count( $types );
        
            for( $i = 0; $i < $type_count; $i++ )
            {
                $id = $this->service->createId( $types[ $i ], $id_string );
                $operation = new stdClass();
                $read_op   = new stdClass();
    
                $read_op->identifier = $id;
                $operation->read     = $read_op;
                $operations[]        = $operation;
            }
        
            $this->service->batch( $operations );
        
            $reply_array = $this->service->getReply()->batchReturn;
            
            if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $reply_array ); }
        
            for( $j = 0; $j < $type_count; $j++ )
            {
                if( $reply_array[ $j ]->readResult->success == 'true' )
                {
                    foreach( T::$type_property_name_map as $type => $property )
                    {
                        //echo "$type => $property" . BR;
                        if( $reply_array[ $j ]->readResult->asset->$property != NULL )
                        {
                            return $type;
                        }
                    }
                }
            }
        }
        return NULL;
    }
    
    private $id;
    private $name;
    private $block_id;
    private $block_path;
    private $block_recycled;
    private $no_block;
    private $format_id;
    private $format_path;
    private $format_recycled;
    private $no_format;
    private $service;
}
?>
