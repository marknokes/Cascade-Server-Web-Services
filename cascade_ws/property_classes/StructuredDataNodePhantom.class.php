<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 9/25/2014 Added isMultiLineNode.
 */
class StructuredDataNodePhantom extends Property
{
    const DEBUG = false;

    const DELIMITER          = DataDefinition::DELIMITER;
    const CHECKBOX_PREFIX    = '::CONTENT-XML-CHECKBOX::';
    const SELECTOR_PREFIX    = '::CONTENT-XML-SELECTOR::';
    const TEXT_TYPE_CALENDAR = "calendar";
    const TEXT_TYPE_CHECKBOX = "checkbox";
    const TEXT_TYPE_DATETIME = "datetime";
    const TEXT_TYPE_DROPDOWN = "dropdown";
    const TEXT_TYPE_RADIO    = "radiobutton";
    const TEXT_TYPE_SELECTOR = "multi-selector";
    
    public function __construct( stdClass $node, DataDefinition $dd, $index, $parent_id='' ) 
    {
        if( $node != NULL ) // $node always a single non-NULL object
        {
            $this->parent_id       = $parent_id;
            $this->type            = $node->type;
            $this->data_definition = $dd;
            $this->node_map        = array();
            
            // attach parent identifier to current node identifier
            // note that parent_id ends with a semi-colon
            $this->identifier = $parent_id . $node->identifier;
            
            // check if this is a multiple field
            $field_identifier = self::getFieldIdentifier( $this->identifier );
            //$field            = $this->data_definition->getField( $field_identifier );
            
            if( isset( $field[ 'multiple' ] ) )
            {
                $this->multiple = $field[ 'multiple' ];
            }
            
            // store the items for radio, multi-selectors, and so on
            if( isset( $field[ 'items' ] ) )
            {
                $this->items = $field[ 'items' ];
            }
            
            // is it required?
            if( isset( $field[ 'required' ] ) )
            {
                $this->required = $field[ 'required' ];
            }
            
            // type mostly for setText
            if( isset( $field[ 'type' ] ) )
            {
                $this->text_type = $field[ 'type' ];
            }
            
            // is it multi-line?
            if( isset( $field[ 'multi-line' ] ) )
            {
                $this->multi_line = $field[ 'multi-line' ];
            }
            
            // is it wysiwyg?
            if( isset( $field[ 'wysiwyg' ] ) )
            {
                $this->wysiwyg = $field[ 'wysiwyg' ];
            }
            
            // add the index if this is a multiple field
            if( $this->multiple == true )
            {
                $this->index       = $index;
                $this->identifier .= self::DELIMITER . $this->index;
            }
            
            if( $this->type != T::GROUP ) // text or asset
            {
                $this->structured_data_nodes = NULL;
                $this->text         = $node->text;
                $this->asset_type   = $node->assetType;
                $this->block_id     = $node->blockId;
                $this->block_path   = $node->blockPath;
                $this->file_id      = $node->fileId;
                $this->file_path    = $node->filePath;
                $this->page_id      = $node->pageId;
                $this->page_path    = $node->pagePath;
                $this->symlink_id   = $node->symlinkId;
                $this->symlink_path = $node->symlinkPath;
                $this->node_map     = array( $this->identifier => $this );
            }
            else // group
            {
                $this->structured_data_nodes = array();
                $this->text         = NULL;
                $this->asset_type   = NULL;
                $this->block_id     = NULL;
                $this->block_path   = NULL;
                $this->file_id      = NULL;
                $this->file_path    = NULL;
                $this->page_id      = NULL;
                $this->page_path    = NULL;
                $this->symlink_id   = NULL;
                $this->symlink_path = NULL;
            
                $cur_identifier     = $this->identifier;
                // make sure there is exactly one trailing delimiter
                $cur_identifier = trim( $cur_identifier, self::DELIMITER );
                $cur_identifier .= self::DELIMITER;
                
                // recursively process the data
                self::processStructuredDataNodePhantoms( 
                    $cur_identifier, // the parent id
                    $this->structured_data_nodes, // array to store children
                    $node->structuredDataNodes->structuredDataNode, // stdClass
                    $this->data_definition
                );
                
                // for easy look-up
                $this->node_map[ $this->identifier ] = $this;
                
                foreach( $this->structured_data_nodes as $child_node )
                {
                    $this->node_map = array_merge( 
                        ( array )$this->node_map, ( array )$child_node->node_map );
                }
            }
        
            $this->recycled = $node->recycled;
        }
    }
    
    public function addChildNode( $node_id )
    {
        if( self::DEBUG ) { echo "SDN::132 " .  S_PRE . /* var_dump( $this->structured_data_nodes ) .*/ E_PRE; }
    
        if( $this->structured_data_nodes == NULL )
        {
            throw new NodeException( "Cannot add a node to a node that has no children" );
        }
        
        // remove digits and semi-colons, turning node id to field id
        $field_id = self::getFieldIdentifier( $node_id );
    	if( self::DEBUG ) { DebugUtility::out( "Node ID: " . $node_id . BR . "Field ID: " . $field_id ); }
        
        if( !$this->data_definition->isMultiple( $field_id ) )
        {
            throw new NodeException( "Cannot add a node to a non-multiple field" );
        }
        
        //$child_count = count( $this->structured_data_nodes );
        $last_pos    = self::getPositionOfLastNode( $this->structured_data_nodes, $node_id );
    	if( self::DEBUG ) { DebugUtility::out( "Last position: " . $last_pos ); }
        
        // create a copy of the last sibling
        $cloned_node = $this->structured_data_nodes[ $last_pos ]->cloneNode();
    	if( self::DEBUG ) { DebugUtility::dump( $cloned_node->toStdClass() ); }

        // new node to be inserted in the middle
        if( $child_count > $last_pos + 1 )
        {
            $before = array_slice( $this->structured_data_nodes, 0, $last_pos + 1 );
            $after  = array_slice( $this->structured_data_nodes, $last_pos + 1 );
            $this->structured_data_nodes = array_merge( $before, array( $cloned_node ), $after );
        }
        else // new node appended at the end
        {
            $this->structured_data_nodes[] = $cloned_node;
        }
        
        $this->node_map = array_merge( 
            $this->node_map, array( $cloned_node->getIdentifier() => $cloned_node ) );

        return $this;
    }
    
    public function cloneNode()
    {
        // clone the calling node
    	if( self::DEBUG ) { DebugUtility::out( "Parent ID: " . $this->parent_id ); }
        
        $clone_obj = new StructuredDataNodePhantom( 
            $this->toStdClass(), $this->data_definition, 0, $this->parent_id );
    	if( self::DEBUG ) { DebugUtility::dump( $clone_obj->toStdClass() ); }
        
        // work out the new identifier
        $this_identifier       = $this->identifier;
    	if( self::DEBUG ) { DebugUtility::out( $this_identifier ); }
        $index                 = self::getLastIndex( $this->identifier ) + 1;
        $clone_identifier      = self::removeLastIndex( $this->identifier ) . 
                                 self::DELIMITER . $index;
        $clone_obj->identifier = $clone_identifier;
    	if( self::DEBUG ) { DebugUtility::out( $clone_identifier ); }
        
        return $clone_obj;
    }
    
    public function display()
    {
        switch( $this->type )
        {
            case T::ASSET:
                break;
                
            case T::GROUP:
                echo "Type: " . $this->type . BR .
                    "Identifier: " . $this->identifier . BR;
                break;
                
            case T::TEXT:
                echo "Type: " . $this->type . BR .
                    "Identifier: " . $this->identifier . BR;
                break;
        }
        return $this;
    }
    
    public function dump()
    {
        echo S_PRE;
        var_dump( $this );
        echo S_PRE;
        return $this;
    }
    
    public function getAssetType()
    {
        return $this->asset_type;
    }
    
    public function getBlockId()
    {
        return $this->block_id;
    }
    
    public function getBlockPath()
    {
        return $this->block_path;
    }
    
    public function getChildren()
    {
        return $this->getStructuredDataNodePhantoms();
    }
    
    public function getDataDefinition()
    {
        return $this->data_definition;
    }
    
    public function getFileId()
    {
        return $this->file_id;
    }
    
    public function getFilePath()
    {
        return $this->file_path;
    }
    
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    public function getIdentifierNodeMap()
    {
        return $this->node_map;
    }
    
    public function getItems()
    {
        return $this->items;
    }
    
    public function getLinkableId()
    {
        if( $this->file_id != NULL )
            return $this->file_id;
        else if( $this->page_id != NULL )
            return $this->page_id;
        else // NULL or not 
            return $this->symlink_id;
    }
    
    public function getLinkablePath()
    {
        if( $this->file_path != NULL )
            return $this->file_path;
        else if( $this->page_path != NULL )
            return $this->page_path;
        else // NULL or not
            return $this->symlink_path;
    }
    
    public function getPageId()
    {
        return $this->page_id;
    }
    
    public function getPagePath()
    {
        return $this->page_path;
    }
    
    public function getParentId()
    {
        return trim( $this->parent_id, self::DELIMITER );
    }
    
    public function getRecycled()
    {
        return $this->recycled;
    }
    
    public function getStructuredDataNodePhantoms()
    {
        return $this->structured_data_nodes;
    }
    
    public function getSymlinkId()
    {
        return $this->symlink_id;
    }
    
    public function getSymlinkPath()
    {
        return $this->symlink_path;
    }
    
    public function getText()
    {
        return $this->text;
    }
    
    public function getTextNodeType()
    {
        return $this->text_type;
    }
    
    public function getTextType()
    {
        return $this->getTextNodeType();
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function hasItem( $item )
    {
        if( $this->items == '' )
            return false;
            
        $items = explode( self::DELIMITER, $this->items );
        return in_array( $item, $items );
    }
    
    public function isAssetNode()
    {
        return $this->type == T::ASSET;
    }
    
    public function isGroupNode()
    {
        return $this->type == T::GROUP;
    }
    
    public function isMultiLineNode()
    {
        if( $this->multi_line )
            return true;
        else
            return false;
    }
    
    public function isMultiple()
    {
        if( $this->multiple )
            return true;
        else
            return false;
    }
    
    public function isRequired()
    {
        return $this->required;
    }

    public function isTextNode()
    {
        return $this->type == T::TEXT;
    }
    
    public function isWYSIWYG()
    {
        if( $this->wysiwyg )
            return true;
        else
            return false;
    }
    
    public function removeLastChildNode( $node_id )
    {
        if( $this->structured_data_nodes == NULL )
        {
            throw new NodeException( "Cannot remove a node from a node that has no children" );
        }
        
        // remove digits and semi-colons
        $field_id = self::getFieldIdentifier( $node_id );
    	if( self::DEBUG ) { DebugUtility::out( "Field ID: " . $field_id ); }
        if( !$this->data_definition->isMultiple( $field_id ) )
            throw new NodeException( "Cannot remove a node from a non-multiple field" );

        $last_pos     = self::getPositionOfLastNode( $this->structured_data_nodes, $node_id );
        $first_pos    = self::getPositionOfFirstNode( $this->structured_data_nodes, $node_id );
    	if( self::DEBUG ) { DebugUtility::out( "First position: " . $first_pos . BR . "Last position: " . $last_pos ); }
        $last_node_id = $this->structured_data_nodes[ $last_pos ]->getIdentifier();
    	if( self::DEBUG ) { DebugUtility::out( "Last node ID: " . $last_node_id ); }
            
        if( $first_pos == $last_pos ) // the only node
            throw new NodeException( "Cannot remove the only node in the field" );
        
        $child_count = count( $this->structured_data_nodes ); // total children
    
        // node to be deleted in the middle
        if( $child_count > $last_pos + 1 )
        {
            $before = array_slice( $this->structured_data_nodes, 0, $last_pos );
            $after = array_slice( $this->structured_data_nodes, $last_pos + 1 );
            $this->structured_data_nodes = array_merge( $before, $after );
        }
        else // at the end
        {
            array_pop( $this->structured_data_nodes );
        }
            
        unset( $this->node_map, $last_node_id );
        
        return $this;
    }

    public function setBlock( Block $block=NULL )
    {
        if( self::DEBUG ) { DebugUtility::out( "setBlock called: " . $block->getId() ); }

        if( $this->asset_type != T::BLOCK )
        {
            throw new NodeException( "The asset does not accept a block." );
        }
        
        if( $block != NULL )
        {
            $this->block_id   = $block->getId();
            $this->block_path = $block->getPath();
        }
        else
        {
            $this->block_id   = NULL;
            $this->block_path = NULL;
        }
        
        return $this;
    }
    
    public function setFile( File $file=NULL )
    {
        if( $this->asset_type != T::FILE )
        {
            throw new NodeException( "The asset does not accept a file." );
        }
        
        if( $file != NULL )
        {
            $this->file_id   = $file->getId();
            $this->file_path = $file->getPath();
        }
        else
        {
            $this->file_id   = NULL;
            $this->file_path = NULL;
        }
        
        return $this;
    }
    
    public function setLinkable( Linkable $linkable=NULL )
    {
        if( $this->asset_type != T::PFS )
        {
            throw new NodeException( "The asset does not accept a linkable." );
        }
        
        if( $linkable != NULL )
        {
            $type = $linkable->getType();
            
            if( $type == T::FILE )
            {
                $this->file_id      = $linkable->getId();
                $this->file_path    = $linkable->getPath();
                $this->page_id      = NULL;
                $this->page_path    = NULL;
                $this->symlink_id   = NULL;
                $this->symlink_path = NULL;
            }
            else if( $type == T::PAGE )
            {
                $this->page_id      = $linkable->getId();
                $this->page_path    = $linkable->getPath();
                $this->file_id      = NULL;
                $this->file_path    = NULL;
                $this->symlink_id   = NULL;
                $this->symlink_path = NULL;
            }
            else if( $type == T::SYMLINK )
            {
                $this->symlink_id   = $linkable->getId();
                $this->symlink_path = $linkable->getPath();
                $this->file_id      = NULL;
                $this->file_path    = NULL;
                $this->page_id      = NULL;
                $this->page_path    = NULL;
            }
        }
        else
        {
            $this->file_id      = NULL;
            $this->file_path    = NULL;
            $this->page_id      = NULL;
            $this->page_path    = NULL;
            $this->symlink_id   = NULL;
            $this->symlink_path = NULL;
        }
        
        return $this;
    }
    
    public function setPage( Page $page=NULL )
    {
        if( $this->asset_type != T::PAGE )
        {
            throw new NodeException( "The asset does not accept a page." );
        }
        
        if( $page != NULL )
        {
            $this->page_id   = $page->getId();
            $this->page_path = $page->getPath();
        }
        else
        {
            $this->page_id   = NULL;
            $this->page_path = NULL;
        }
        
        return $this;
    }
    
    public function setSymlink( Symlink $symlink=NULL )
    {
        if( $this->asset_type != T::SYMLINK )
        {
            throw new NodeException( "The asset does not accept a symlink." );
        }
        
        if( $symlink != NULL )
        {
            $this->symlink_id   = $symlink->getId();
            $this->symlink_path = $symlink->getPath();
        }
        else
        {
            $this->symlink_id   = NULL;
            $this->symlink_path = NULL;
        }
        
        return $this;
    }
    
    public function setText( $text )
    {
        $text = trim( $text );
        
        // required
        if( $this->required && $text == '' )
        {
            throw new EmptyValueException( "The text cannot be empty" );
        }
        // no text to group
        if( $this->type == T::GROUP )
        {
            throw new NodeException( "Group cannot have text" );
        }
        else if( $this->items == '' ) // normal text, datetime, calendar
        {
            if( $this->text_type == self::TEXT_TYPE_DATETIME )
            {
                if( !is_numeric( $text) )
                    throw new UnacceptableValueException( 
                        "$text is not an acceptable datetime value." );
                    
                $this->text = $text;
            }
            else if( $this->text_type == self::TEXT_TYPE_CALENDAR ) // month-day-year
            {
                $date_array = explode( '-', $text );
                
                // must have three parts
                if( count( $date_array ) != 3 )
                {
                    throw new UnacceptableValueException( 
                        "$text is not an acceptable date value." );
                }
                
                list( $month, $day, $year ) = $date_array;
                
                // convert strings to integers
                $month = intval( $month );
                $day   = intval( $day );
                $year  = intval( $year );
                
                // check the date
                if( !checkdate( $month, $day, $year ) )
                {
                    throw new UnacceptableValueException( 
                        "$text is not an acceptable date value." );
                }
                
                // compare years, Cascade only has a range of 20 years
                $today     = getdate();
                $this_year = $today[ 'year' ];
                
                if( abs( $this_year - $year ) > 10 )
                {
                    throw new UnacceptableValueException( 
                        "$text is not an acceptable date value." );
                }
                
                // convert integers back to strings
                if( $month < 10 )
                {
                    $month_string = '0' . $month;
                }
                else
                {
                    $month_string = $month;
                }
                
                if( $day < 10 )
                {
                    $day_string = '0' . $day;
                }
                else
                {
                    $day_string = $day;
                }
                
                $this->text = $month_string . '-' . $day_string . '-' . $year;
            }
            else
            {
                $this->text = $text;
            }
            
            return $this;
        }
        else // checkbox, radio, select, dropdown
        {
            $item_array = explode( self::DELIMITER, $this->items ); // could be NULL
            
            if( strpos( $text, self::CHECKBOX_PREFIX ) !== false ) // no semi-colon
            {
                $input_array = explode( self::CHECKBOX_PREFIX, $text );
            }
            else if( strpos( $text, self::SELECTOR_PREFIX ) !== false ) // no semi-colon
            {
                $input_array = explode( self::SELECTOR_PREFIX, $text );
            }
            else // with semi-colon
            {
                $input_array = explode( self::DELIMITER, $text );
            }
            
            if( count( $item_array) == 1 )  // single item checkbox or dropdown
            {
                if( $this->text_type == self::TEXT_TYPE_CHECKBOX )
                {
                    // unacceptable input
                    if( $text != $this->items && $text != '' && $text != self::CHECKBOX_PREFIX )
                    {
                        throw new NoSuchValueException( "The value $text does not exist" );
                    }
                    else if( $text == '' || $text == self::CHECKBOX_PREFIX )
                    {
                        $this->text = self::CHECKBOX_PREFIX;
                    }
                    else
                    {
                        $this->text = $text;
                    }
                }
                else if( $this->text_type == self::TEXT_TYPE_DROPDOWN )
                {
                    if( !in_array( $text, $item_array ) )
                    {
                        throw new NoSuchValueException( "The value $text does not exist." );
                    }
                    $this->text = $text;
                }
                
                return $this;
            }
            else // multiple items
            {
                if( $this->text_type == self::TEXT_TYPE_CHECKBOX )
                {
                    if( $text == '' || $text == self::CHECKBOX_PREFIX )
                    {
                        $this->text = self::CHECKBOX_PREFIX;
                    }
                    else
                    {
                        $temp = '';
                        
                        foreach( $input_array as $input )
                        {
                            if( $input == '' )
                            {
                                continue;
                            }
                            else if( !in_array( $input, $item_array ) )
                            {
                                throw new NoSuchValueException( 
                                    "The value $input does not exist." );
                            }
                            else
                            {
                                $temp .= self::CHECKBOX_PREFIX . $input;
                            }
                        }
                    
                        $this->text = $temp;
                    }
                    return $this;
                }
                else if( $this->text_type == self::TEXT_TYPE_RADIO )
                {
                    if( count( $input_array ) > 1 )
                    {
                        throw new UnacceptableValueException( 
                            "Radio button does not allow more than one value." );
                    }
                
                    if( !in_array( $text, $item_array ) )
                    {
                        throw new NoSuchValueException( "The value $text does not exist" );
                    }
                    
                    $this->text = $text;
                    
                    return $this;
                }
                else if( $this->text_type == self::TEXT_TYPE_SELECTOR )
                {
                    if( $text == '' || $text == self::SELECTOR_PREFIX )
                    {
                        $this->text = self::SELECTOR_PREFIX;
                    }
                    else
                    {
                        $temp = '';
                        
                        foreach( $input_array as $input )
                        {
                            // skip empty string
                            if( $input == '' )
                            {
                                continue;
                            }
                            else if( !in_array( $input, $item_array ) )
                            {
                                throw new UnacceptableValueException( 
                                    "The value $input does not exist." );
                            }
                            else
                            {
                                $temp .= self::SELECTOR_PREFIX . $input;
                            }
                        }
                        
                        $this->text = $temp;
                    }
                    return $this;
                }
            }
        }
    }

    public function swapChildren( $pos1, $node1, $pos2, $node2 )
    {
        $this->structured_data_nodes[ $pos1 ] = $node1;
        $this->structured_data_nodes[ $pos2 ] = $node2;
        return $this;
    }
    
    public function toStdClass()
    {
        $obj       = new stdClass();
        $obj->type = $this->type;
        $id_array  = explode( self::DELIMITER, $this->identifier );
        $id_count  = count( $id_array );
        
        // work out the identifier of the node
        $id = $id_array[ $id_count - 1 ];
        
        if( is_numeric( $id ) )
        {
            $obj->identifier = $id_array[ $id_count - 2 ];
        }
        else
        {
            $obj->identifier = $id_array[ $id_count - 1 ];
        }
    
        if( $this->type == T::GROUP )
        {
            $node_count = count( $this->structured_data_nodes );
        
            if( $node_count == 1 )
            {
                $obj->structuredDataNodes->structuredDataNode =
                    $this->structured_data_nodes[0]->toStdClass();
            }
            else
            {
                $obj->structuredDataNodes->structuredDataNode = array();
        
                for( $i = 0; $i < $node_count; $i++ )
                {
                    $obj->structuredDataNodes->structuredDataNode[] = 
                        $this->structured_data_nodes[$i]->toStdClass();
                }
            }
        }
        else
        {
            $obj->structuredDataNodes = NULL;
        }
    
        $obj->text        = $this->text;
        $obj->assetType   = $this->asset_type;
        $obj->blockId     = $this->block_id;
        $obj->blockPath   = $this->block_path;
        $obj->fileId      = $this->file_id;
        $obj->filePath    = $this->file_path;
        $obj->pageId      = $this->page_id;
        $obj->pagePath    = $this->page_path;
        $obj->symlinkId   = $this->symlink_id;
        $obj->symlinkPath = $this->symlink_path;
        $obj->recycled    = $this->recycled;
        
        return $obj;
    }
    
    public static function getFieldIdentifier( $node_id )
    {
        /* this code looks unnecessarily long; just to make sure 
           only digits surrounded by ; are removed
           see StringUtility::getFullyQualifiedIdentifierWithoutPositions
        */
        // remove digit;
        $field_id = preg_replace( '/;(\d)+/', ';', $node_id );
        // remove any doubled-semi-colons
        $field_id = str_replace( ';;', ';', $field_id );
        // trim last semi-colon
        $field_id = trim( $field_id, ';' );
        return $field_id;
    }

    public static function getLastIndex( $node_id )
    {
        $matches = array();
        $result = preg_match( '/;(\d+)$/', $node_id, $matches );
        
        if( $result )
        {
            return intval( $matches[ 1 ] );
        }
        return -1;
    }
    
    public static function getPositionOfFirstNode( $array, $field_id )
    {
        $child_count = count( $array );
        
        for( $i = 0; $i < $child_count; $i++ )
        {
            if( strpos( $array[ $i ]->getIdentifier(), $field_id . DataDefinition::DELIMITER ) !== false )
                break;
        }
        return $i;
    }
    
    public static function getPositionOfLastNode( $array, $node_id )
    {
        $child_count = count( $array );
        if( self::DEBUG ) { DebugUtility::out( "Child count: " . $child_count ); }
        $shared_id   = self::removeLastIndex( $node_id );
        if( self::DEBUG ) { DebugUtility::out( "Shared ID: " . $shared_id ); }
        
        for( $i = $child_count - 1; $i > 0; $i-- )
        {
			if( self::DEBUG ) { DebugUtility::out( "Child ID: " . $array[ $i ]->getIdentifier() ); }            
            if( strpos( $array[ $i ]->getIdentifier(), $shared_id ) !== false )
            {
            	if( self::DEBUG ) { DebugUtility::out( "Found in $i" ); }  
                break;
            }
        }
        return $i;
    }
    
    public static function processStructuredDataNodePhantoms( 
    	$parent_id, &$node_array, $node_std, $data_definition )
    {
        if( self::DEBUG ) { DebugUtility::out( "Parent ID: " . $parent_id ); }  
        
        if( !is_array( $node_std ) )
        {
            $node_std = array( $node_std );
        }
        
        $node_count  = count( $node_std );
        if( self::DEBUG ) { DebugUtility::out( "Node count: " . $node_count ); }  
        
        // these are used to calculate the index
        $previous_identifier;
        $current_identifier;
        $cur_index = 0;
        
        // work out the id of the current node for the data definition
        // no digits in the fully qualified identifiers
        for( $i = 0; $i < $node_count; $i++ )
        {
            $fq_identifier = $node_std[$i]->identifier;
            
            if( $parent_id != '' )
            {
                $parent_id_array = explode( self::DELIMITER, $parent_id );
                $temp            = '';
                
                foreach( $parent_id_array as $part )
                {
                    if( !is_numeric( $part ) )
                    {
                        $temp .= $part . self::DELIMITER;
                    }
                }
                
                $temp          = trim( $temp, self::DELIMITER );
                $fq_identifier = 
                    $temp . self::DELIMITER . $node_std[$i]->identifier;
            }
        
            //$is_multiple         = $data_definition->isMultiple( $fq_identifier );
			if( isset( $current_identifier ) )
            	$previous_identifier = $current_identifier;
            $current_identifier  = $node_std[$i]->identifier;
            
            // a multiple text or group, work out fully qualified identifier
            if( $is_multiple )
            {
            	// an old one, keep counting
                if( isset( $previous_identifier ) && $previous_identifier == $current_identifier ) 
                {
                    $cur_index++;
                }
                else // a new one, start from 0 again
                {
                    $cur_index = 0;
                }
            }
            
            if( $parent_id != '' )
            {
                $n = new StructuredDataNodePhantom( 
                	$node_std[$i], $data_definition, $cur_index, $parent_id );
            }
            else
            {
                $n = new StructuredDataNodePhantom( 
                	$node_std[$i], $data_definition, $cur_index );
            }
            
            $n->parent_id = $parent_id;
            
            $node_array[ $i ] = $n;
        }
    }
    
    public static function removeLastIndex( $node_id )
    {
        return preg_replace( '/;(\d)+$/', '', $node_id );
    }

    private $type;                  // asset, group, text
    private $identifier;            // fully qualified identifier
    private $structured_data_nodes; // children of a group
    private $text;
    private $asset_type;
    private $block_id;
    private $block_path;
    private $file_id;
    private $file_path;
    private $multi_line;
    private $page_id;
    private $page_path;
    private $symlink_id;
    private $symlink_path;
    private $recycled;
    private $parent_id;
    private $multiple;  // whether this is a multiple node
    private $required;  // whether value is required
    private $text_type; // type of text, radiobutton, dropdown, and so on
    private $index;     // index of a multiple field
    private $items;     // items string of radio, checkbox, dropdown & selector
    private $wysiwyg;   // whether this is a wysiwyg
    private $data_definition;
    private $node_map;
}
?>
