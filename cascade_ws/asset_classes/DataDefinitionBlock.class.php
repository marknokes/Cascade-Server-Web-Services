<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 2/24/2015 Added getPossibleValues.
  * 9/29/2014 Fixed in bug in edit.
  * 9/25/2014 Added isMultiLineNode.
  * 8/14/2014 Added style to error messages.
 */
class DataDefinitionBlock extends Block
{
    const DEBUG = false;
    const DUMP  = false;
    const TYPE  = T::DATABLOCK;

    /**
    * The constructor
    * @param $service the AssetOperationHandlerService object
    * @param $identifier the identifier object
    */
    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        if( isset( $this->getProperty()->structuredData ) )
        {
            $this->processStructuredData();
        }
        else
        {
            $this->xhtml = $this->getProperty()->xhtml;
        }
    }

    public function appendSibling( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        if( self::DEBUG ) { DebugUtility::out( "Calling appendSibling" ); }
        $this->structured_data->appendSibling( $identifier );
        $this->edit();
        return $this;
    }
    
    public function copyDataTo( $block )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $block->setStructuredData( $this->getStructuredData() );
        return $this;
    }
    
    public function createNInstancesForMultipleField( $number, $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $number = intval( $number );
        
        if( !$number > 0 )
        {
            throw new UnacceptableValueException( "The value $number is not a number." );
        }
        
        if( !$this->hasNode( $identifier ) )
        {
            throw new NodeException( "The node $identifier does not exist." );
        }
        
        $num_of_instances  = $this->getNumberOfSiblings( $identifier );
    
        if( $num_of_instances < $number ) // more needed
        {
            while( $this->getNumberOfSiblings( $identifier ) != $number )
            {
                $this->appendSibling( $identifier );
            }
        }
        else if( $num_of_instances > $number )
        {
            while( $this->getNumberOfSiblings( $identifier ) != $number )
            {
                $this->removeLastSibling( $identifier );
            }
        }
        $this->reloadProperty();
        $this->processStructuredData();
        return $this;
    }
    
    public function displayDataDefinition()
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->getDataDefinition()->displayXML();
        return $this;
    }
    
    public function displayXhtml()
    {
        if( !$this->hasStructuredData() )
        {
            $xhtml_string = XMLUtility::replaceBrackets( $this->xhtml );
            echo S_H2 . 'XHTML' . E_H2;
            echo $xhtml_string . HR;
        }
        return $this;
    }
    
    public function edit()
    {
        // edit the asset
        $asset = new stdClass();
        $block = $this->getProperty();
        
        $block->metadata = $this->getMetadata()->toStdClass();
        
        if( $this->structured_data != NULL )
        {
            $block->structuredData = $this->structured_data->toStdClass();
            $block->xhtml          = NULL;
        }
        else
        {
            $block->structuredData = NULL;
            $block->xhtml          = $this->xhtml;
        }

        $asset->{ $p = $this->getPropertyName() } = $block;
        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
        	if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $asset ); }
            throw new EditingFailureException(
            	"<span style='color:red;font-weight:bold;'>Block: " . 
            	$this->getPath() . "</span>" . BR .
                M::EDIT_ASSET_FAILURE . $service->getMessage() );
        }
        $this->reloadProperty();
        
        if( isset( $this->getProperty()->structuredData ) )
        	$this->processStructuredData();
        return $this;
    }
    
    public function getAssetNodeType( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getAssetNodeType( $identifier );
    }

    public function getBlockId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getBlockId( $identifier );
    }
    
    public function getBlockPath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getBlockPath( $identifier );
    }
    
    public function getDataDefinition()
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getDataDefinition();
    }
    
    public function getFileId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getFileId( $identifier );
    }
    
    public function getFilePath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getFilePath( $identifier );
    }
    
    public function getIdentifiers()
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getIdentifiers();
    }
    
    public function getLinkableId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getLinkableId( $identifier );
    }
    
    public function getLinkablePath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getLinkablePath( $identifier );
    }
    
    public function getNodeType( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getNodeType( $identifier );
    }
    
    public function getNumberOfSiblings( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        if( trim( $identifier ) == "" )
        {
            throw new EmptyValueException( M::EMPTY_IDENTIFIER );
        }
        
        if( !$this->hasIdentifier( $identifier ) )
        {
            throw new NodeException( "The node $identifier does not exist" );
        }
        return $this->structured_data->getNumberOfSiblings( $identifier );
    }

    public function getPageId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getPageId( $identifier );
    }
    
    public function getPagePath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getPagePath( $identifier );
    }
    
    public function getPossibleValues( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getPossibleValues( $identifier );
    }
    
    public function getStructuredData()
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data;
    }
    
    public function getSymlinkId( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getSymlinkId( $identifier );
    }
    
    public function getSymlinkPath( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getSymlinkPath( $identifier );
    }
    
    public function getText( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getText( $identifier );
    }
    
    public function getTextNodeType( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->getTextNodeType( $identifier );
    }

    public function getXhtml()
    {
        return $this->xhtml;
    }
    
    public function hasIdentifier( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK . " " . $this->getPath() );
        }
        
        return $this->hasNode( $identifier );
    }
    
    public function hasNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->hasNode( $identifier );
    }
    
    public function hasStructuredData()
    {
        return $this->structured_data != NULL;
    }
    
    public function isAssetNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->isAssetNode( $identifier );
    }
    
    public function isGroupNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->isGroupNode( $identifier );
    }
    
    public function isMultiLineNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->isMultiLineNode( $identifier );
    }
    
    public function isMultiple( $field_name )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->getDataDefinition()->isMultiple( $field_name );
    }
    
    public function isRequired( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->isRequired( $identifier );
    }

    public function isTextNode( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->isTextNode( $identifier );
    }
    
    public function isWYSIWYG( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->isWYSIWYG( $identifier );
    }

    public function removeLastSibling( $identifier )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->removeLastSibling( $identifier );
        $this->edit();
        return $this;
    }
    
    public function replaceByPattern( $pattern, $replace, $include=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->replaceByPattern( $pattern, $replace, $include );
        return $this;
    }
    
    public function replaceXhtmlByPattern( $pattern, $replace )
    {
        if( $this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_XHTML_BLOCK );
        }
        
        $this->xhtml = preg_replace( $pattern, $replace, $this->xhtml );
        
        return $this;
    }
    
    public function replaceText( $search, $replace, $include=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->replaceText( $search, $replace, $include );
        return $this;
    }
    
    public function searchText( $string )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        return $this->structured_data->searchText( $string );
    }
    
    public function searchXhtml( $string )
    {
        if( $this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_XHTML_BLOCK );
        }

        return strpos( $this->xhtml, $string ) !== false;
    }

    public function setBlock( $identifier, Block $block=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->setBlock( $identifier, $block );
        return $this;
    }
    
    public function setFile( $identifier, File $file=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->setFile( $identifier, $file );
        return $this;
    }

    public function setLinkable( $identifier, Linkable $linkable=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->setLinkable( $identifier, $linkable );
        return $this;
    }

    public function setPage( $identifier, Page $page=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->setPage( $identifier, $page );
        return $this;
    }
    
    public function setStructuredData( StructuredData $structured_data )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        $this->structured_data = $structured_data;
        $this->edit();
        $this->processStructuredData();
        return $this;
    }

    public function setSymlink( $identifier, Symlink $symlink=NULL )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->setSymlink( $identifier, $symlink );
        return $this;
    }

    public function setText( $identifier, $text )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->setText( $identifier, $text );
        return $this;
    }
    
    public function setXhtml( $xhtml )
    {
        if( !$this->hasStructuredData() )
        {
            $this->xhtml = $xhtml;
        }
        else
        {
            throw new WrongBlockTypeException( M::NOT_XHTML_BLOCK );
        }
        return $this;
    }

    public function swapData( $identifier1, $identifier2 )
    {
        if( !$this->hasStructuredData() )
        {
            throw new WrongBlockTypeException( M::NOT_DATA_BLOCK );
        }
        
        $this->structured_data->swapData( $identifier1, $identifier2 );
        $this->edit()->processStructuredData();
        
        return $this;
    }

    private function processStructuredData()
    {
        $this->structured_data = new StructuredData( 
            $this->getProperty()->structuredData, 
            $this->getService() );
    }

    private $structured_data;
    private $xhtml;
}
?>
