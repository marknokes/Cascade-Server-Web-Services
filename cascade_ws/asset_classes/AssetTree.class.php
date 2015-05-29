<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 8/18/2014 Added NULL function in traverse for Report.
  * 7/14/2014 Changed applyFunctionsToChild to accept class static methods.
  * 6/23/2014 Added an optional parameter passed into toXml to generate lastmod.
 */
class AssetTree
{
    const DEBUG = false;

    public function __construct( Container $container )
    {
        if( $container == NULL )
        {
            throw new NullAssetException( M::NULL_CONTAINER );
        }
        $this->root         = $container;
        $root_children      = $container->getChildren();
        $this->has_children = count( $root_children ) > 0;
        
        if( $this->has_children )
        {
            $this->children = array();
            
            foreach( $root_children as $root_child )
            {
                if( $root_child->getType() == $container->getType() )
                {
                    $class_name = T::$type_class_name_map[ $container->getType() ];
                
                    $this->children[] = new AssetTree( 
                        $class_name::getAsset( $this->root->getService(),
                            $container->getType(),
                            $root_child->getId() )
                    );
                }
                else
                {
                    $this->children[] = $root_child;
                }
            }
        }
    }
    
    public function hasChildren()
    {
        return $this->has_children;
    }
    
    public function toListString()
    {
        $list_string = S_UL . S_LI;
        
        $list_string .= $this->root->getType() . " " .
            $this->root->getPath() . " " .
            $this->root->getId();
            
        if( $this->has_children )
        {
            if( get_class( $this->children[ 0 ] ) != get_class() )
                $list_string .= S_UL;
            
            foreach( $this->children as $child )
            {
                if( get_class( $child ) == 'Child' )
                {
                    $list_string .= $child->toLiString();
                }
                else
                {
                    $list_string .= $child->toListString();
                }
            }
            
            if( get_class( $this->children[ 0 ] ) != get_class() )
                $list_string .= E_UL;
        }
        
        $list_string .= E_LI . E_UL;
        
        return $list_string;
    }
    
    public function toXml( $indent="" )
    {
        $xml_string = $indent . "<" . $this->root->getType() . " path=\"" .
            $this->root->getPath() . "\" id=\"" .
            $this->root->getId() . "\"";
            
        $child_indent = $indent . "  ";
            
        if( $this->has_children )
        {
            $xml_string .= ">\n";
            
            foreach( $this->children as $child )
            {
                $xml_string .= $child->toXml( $child_indent, $this->root->getService() );
            }
            $xml_string .= $indent . "</" . $this->root->getType() . ">\n";
        }
        else
        {
            $xml_string .= "/>\n";
        }
        
        return $xml_string;
    }
    
    public function traverse( $function_array, $params=NULL, &$results=NULL )
    {
        $service = $this->root->getService();
        
        // skip root container
        if( $params != NULL && isset( $params[ F::SKIP_ROOT_CONTAINER ] ) && 
            $params[ F::SKIP_ROOT_CONTAINER ] == true )
        {
            // reset flag for child containers in recursion
            $params[ F::SKIP_ROOT_CONTAINER ] = false;
        }
        // process root container as well
        else
        {
            $this->applyFunctionsToChild( 
                $service, $this->root->toChild(), $function_array, $params, $results );
        }
        
        // process children
        if( $this->has_children )
        {
            foreach( $this->children as $child )
            {
                // child is an asset tree
                if( get_class( $child ) != 'Child' )
                {
                    // recursive traversal
                    $child->traverse( $function_array, $params, $results );
                }
                else
                {
                    $this->applyFunctionsToChild( 
                        $service, $child, $function_array, $params, $results );
                }
            }
        }
        return $this;
    }
    
    private function applyFunctionsToChild( 
        AssetOperationHandlerService $service, Child $child, 
        $function_array, $params=NULL, &$results=NULL )
    {
        $type = $child->getType();
        
        // match the type first
        if( isset( $function_array[ $type ] ) )
        {
            $functions  = $function_array[ $type ];
            $func_count = count( $functions );
            
            for( $i = 0; $i < $func_count; $i++ )
            {
                if( $functions[ $i ] == NULL )
            	{
            		continue;
            	}

            	// class static method
            	if( strpos( $functions[ $i ], "::" ) !== false )
            	{
            		$method_array = StringUtility::getExplodedStringArray( ":", $functions[ $i ] );
            		$class_name   = $method_array[ 0 ];
            		$method_name  = $method_array[ 1 ];
            		
                	if( !method_exists( $class_name, $method_name ) )
                	{
                    	throw new NoSuchFunctionException( "The function " . $functions[ $i ] .
                        	" does not exist." );
                    }
            	}
                else if( !function_exists( $functions[ $i ] ) )
                {
                	throw new NoSuchFunctionException( "The function " . $functions[ $i ] .
                        " does not exist." );
                }
            }
            // apply function with parameters and results array
            for( $i = 0; $i < $func_count; $i++ )
            {
                if( $functions[ $i ] == NULL )
            	{
            		continue;
            	}

                if( strpos( $functions[ $i ], "::" ) !== false )
            	{
            		$method_array = StringUtility::getExplodedStringArray( ":", $functions[ $i ] );
            		$class_name   = $method_array[ 0 ];
            		$method_name  = $method_array[ 1 ];
            		$class_name::$method_name( $service, $child, $params, $results );
				}
				else
				{
                	$func_name = $functions[ $i ];
                	$func_name( $service, $child, $params, $results );
                }
            }
        }
    }
    
    private $root;
    private $has_children;
    private $children;
}
?>