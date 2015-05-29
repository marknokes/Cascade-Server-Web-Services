<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 6/2/2014 Added arrays for asset expiration.
 */
class MessageArrays
{
    const DEBUG = false;
    const DUMP  = false;

    // all messages
    public static $all_messages    = array();
    public static $all_message_ids = array();
    // from
    public static $message_ids_from = array();
    // non-system
    public static $non_system_messages = array();
    // expiration
    public static $asset_expiration_message_ids       = array();
    public static $asset_expiration_message           = array();
    // publish
    public static $publish_message_ids                = array();
    public static $publish_message_ids_with_issues    = array();
    public static $publish_message_ids_without_issues = array();
    public static $publish_messages                   = array();
    public static $publish_messages_with_issues       = array();
    public static $publish_messages_without_issues    = array();
    // unpublish
    public static $unpublish_message_ids                = array();
    public static $unpublish_message_ids_with_issues    = array();
    public static $unpublish_message_ids_without_issues = array();
    public static $unpublish_messages                   = array();
    public static $unpublish_messages_with_issues       = array();
    public static $unpublish_messages_without_issues    = array();
    // summary
    public static $summary_message_ids               = array();
    public static $summary_message_ids_no_failures   = array();
    public static $summary_message_ids_with_failures = array();
    public static $summary_messages                  = array();
    public static $summary_messages_no_failures      = array();
    public static $summary_messages_with_failures    = array();
    // workflow
    public static $workflow_message_ids             = array();
    public static $workflow_message_ids_is_complete = array();
    public static $workflow_message_ids_other       = array();
    public static $workflow_messages                = array();
    public static $workflow_messages_complete       = array();
    public static $workflow_messages_other          = array();
    // other
    public static $other_messages    = array();
    public static $other_message_ids = array();
    // objects
    public static $id_obj_map = array();
    
    public static function initialize( AssetOperationHandlerService $service )
    {
        try
        {
            $service->listMessages();

            if( $service->isSuccessful() )
            {
                $messages = $service->getListedMessages();
                $temp_msg = array();
        
                if( !( $messages->message == NULL ) )
                {
                    if( !is_array( $messages->message ) )
                    {
                        $temp_msg[] = $messages->message;
                    }
                    else
                    {
                        $temp_msg = $messages->message;
                    }

                    foreach( $temp_msg as $message )
                    {
                        $id      = $message->id;
                        $to      = $message->to;
                        $from    = $message->from;
                        $date    = $message->date;
                        $subject = trim( $message->subject );
                        $body    = $message->body;
            
                        self::$all_message_ids[] = $id;
                        $message_obj             = new Message( $message );
                        
                        // store all messages
                        self::$all_messages[]    = $message_obj;
                        self::$id_obj_map[ $id ] = $message_obj;
                
                        // from whom?
                        if( !isset( $message_ids_from[ $from ] ) )
                        {
                            self::$message_ids_from[ $from ] = array();
                        }

                        self::$message_ids_from[ $from ][] = $id;
                
                        if( $from != 'system' )
                        {
                            self::$non_system_messages[] = $message_obj;
                        }
                        
                        if( self::DEBUG ) { DebugUtility::out( $message_obj->getType() ); }
                
                        if( $message_obj->getType() == Message::TYPE_EXPIRATION )
                        {
                            self::$asset_expiration_message[]     = $message_obj;
                            self::$asset_expiration_message_ids[] = $id;
                        }
                        // publish messages
                        else if( $message_obj->getType() == Message::TYPE_PUBLISH )
                        {
                            self::$publish_messages[]    = $message_obj;
                            self::$publish_message_ids[] = $id;
                            
                            // no issues
                            if( strpos( $subject, "(0 issue(s))" ) !== false )
                            {
                                if( self::DEBUG ) { echo "L::121 " . $id . BR; }
                                self::$publish_message_ids_without_issues[] = $id;
                                self::$publish_messages_without_issues[]    = $message_obj;
                            }
                            else
                            {
                                if( self::DEBUG ) { echo "L::124 " . $id . BR; }
                                self::$publish_message_ids_with_issues[] = $id;
                                self::$publish_messages_with_issues[]    = $message_obj;
                            }
                        }
                        // unpublish messages
                        else if( $message_obj->getType() == Message::TYPE_UNPUBLISH )
                        {
                            self::$unpublish_messages[]    = $message_obj;
                            self::$unpublish_message_ids[] = $id;
                    
                            // no issues
                            if( strpos( $subject, "(0 issue(s))" ) !== false )
                            {
                                self::$unpublish_message_ids_without_issues[] = $id;
                                self::$unpublish_messages_without_issues[]    = $message_obj;
                            }
                            else
                            {
                                self::$unpublish_message_ids_with_issues[] = $id;
                                self::$unpublish_messages_with_issues[]    = $message_obj;
                            }
                        }
                        // summary
                        else if( $message_obj->getType() == Message::TYPE_SUMMARY )
                        {
                            self::$summary_messages[]    = $message_obj;
                            self::$summary_message_ids[] = $id;
                            
                            // 0 failures
                            if( strpos( $subject, "(0 failures)" ) !== false )
                            {
                                self::$summary_message_ids_no_failures[] = $id;
                                self::$summary_messages_no_failures[]    = $message_obj;
                            }
                            else
                            {
                                self::$summary_message_ids_with_failures[] = $id;
                                self::$summary_messages_with_failures[]    = $message_obj;
                            }
                        }
                        // workflow
                        else if( $message_obj->getType() == Message::TYPE_WORKFLOW )
                        {
                            self::$workflow_messages[]    = $message_obj;
                            self::$workflow_message_ids[] = $id;
                    
                            // is complete
                            if( strpos( $subject, "is complete" ) !== false )
                            {
                                self::$workflow_message_ids_is_complete[] = $id;
                                self::$workflow_messages_complete[]       = $message_obj;
                            }
                            else
                            {
                                self::$workflow_message_ids_other[] = $id;
                                self::$workflow_messages_other[]    = $message_obj;
                            }
                        }
                        // other
                        else
                        {
                            self::$other_messages[]    = $message_obj;
                            self::$other_message_ids[] = $id;
                        }
                        // date
                    }
                    
                    usort( self::$all_messages, 'Message::compare' );
                    usort( self::$publish_messages, 'Message::compare' );
                    usort( self::$unpublish_messages, 'Message::compare' );
                    usort( self::$workflow_messages, 'Message::compare' );
                    usort( self::$other_messages, 'Message::compare' );
                    
                    usort( self::$publish_messages_with_issues, 'Message::compare' );
                    usort( self::$unpublish_messages_with_issues, 'Message::compare' );
                    usort( self::$workflow_messages_other, 'Message::compare' );
                    
                    usort( self::$non_system_messages, 'Message::compare' );
                }
        
                //var_dump( $workflow_message_ids_is_complete );
            }
            else
                echo "Failed to list messages. " . $service->getMessage();
        }
        catch( Exception $e )
        {
            echo S_PRE . $e . E_PRE;
        }
    }
}