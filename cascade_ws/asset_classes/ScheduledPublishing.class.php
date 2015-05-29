<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
abstract class ScheduledPublishing extends ContainedAsset
{
    const DEBUG     = false;
    const SUNDAY    = T::SUNDAY;
    const MONDAY    = T::MONDAY;
    const TUESDAY   = T::TUESDAY;
    const WEDNESDAY = T::WEDNESDAY;
    const THURSDAY  = T::THURSDAY;
    const FRIDAY    = T::FRIDAY;
    const SATURDAY  = T::SATURDAY;
    
    const DEFAULT_TIME = "00:00:00.000";

    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        $this->days_of_week = array( 
            self::SUNDAY, self::MONDAY, 
            self::TUESDAY, self::WEDNESDAY, self::THURSDAY,
            self::FRIDAY, self::SATURDAY
        );
    }
    
    public function addGroupToSendReport( Group $g )
    {
        if( $g == NULL )
            throw new NullAssetException( "The group cannot be NULL." );
        
        $g_name       = $g->getName();
        $group_string = $this->getProperty()->sendReportToGroups;
        $group_array  = explode( ';', $group_string );
        
        if( !in_array( $g_name, $group_array ) )
        {
            $group_array[] = $g_name;
        }
        $group_string = implode( ';', $group_array );
        $this->getProperty()->sendReportToGroups = $group_string;

        return $this;
    }
    
    public function addUserToSendReport( User $u )
    {
        if( $u == NULL )
            throw new NullAssetException( "The user cannot be NULL." );
        
        $u_name      = $u->getName();
        $user_string = $this->getProperty()->sendReportToUsers;
        $user_array  = explode( ';', $user_string );
        
        if( !in_array( $u_name, $user_array ) )
        {
            $user_array[] = $u_name;
        }
        $user_string = implode( ';', $user_array );
        $this->getProperty()->sendReportToUsers = $user_string;

        return $this;
    }

    public function getCronExpression()
    {
        return $this->getProperty()->cronExpression;
    }
    
    public function getDaysOfWeek()
    {
        return $this->days_of_week;
    }
   
    public function getPublishDaysOfWeek()
    {
        return $this->getProperty()->publishDaysOfWeek;
    }
    
    public function getPublishIntervalHours()
    {
        return $this->getProperty()->publishIntervalHours;
    }
  
    public function getSendReportOnErrorOnly()
    {
        return $this->getProperty()->sendReportOnErrorOnly;
    }
    
    public function getSendReportToGroups()
    {
        return $this->getProperty()->sendReportToGroups;
    }
    
    public function getSendReportToUsers()
    {
        return $this->getProperty()->sendReportToUsers;
    }

    public function getTimeToPublish()
    {
        return $this->getProperty()->timeToPublish;
    }
    
    public function getUsesScheduledPublishing()
    {
        return $this->getProperty()->usesScheduledPublishing;
    }
    
    public function setCronExpression( $cron )
    {
        if( $cron != NULL && trim( $cron ) != "" )
        {
            return $this->setScheduledPublishing( true, NULL, NULL, $cron, NULL );
        }
        throw new EmptyValueException( "The cron expression supplied cannot be empty." );
    }
    
    public function setDayOfWeek( $days, $time=NULL )
    {
        return $this->setPublishDayOfWeek( $days, $time=NULL );
    }
    
    public function setIntervalHours( $hours, $time=NULL )
    {
        return $this->setPublishIntervalHours( $hours, $time=NULL );
    }
    
    public function setPublishDayOfWeek( $days, $time=NULL )
    {
        if( $days != NULL )
        {
            return $this->setScheduledPublishing( true, $days, NULL, NULL, $time );
        }
        throw new EmptyValueException( "The days supplied cannot be NULL." );
    }
    
    public function setPublishIntervalHours( $hours, $time=NULL )
    {
        if( $hours != NULL )
        {
            return $this->setScheduledPublishing( true, NULL, $hours, NULL, $time );
        }
        throw new EmptyValueException( "The interval supplied cannot be NULL." );
    }
    
    public function setScheduledPublishing( 
        $uses_scheduled_publishing=false,
        $day_of_week=NULL, 
        $publish_interval_hours=NULL, 
        $cron_expression=NULL, 
        $time_to_publish=NULL
    )
    {
        if( !BooleanValues::isBoolean( $uses_scheduled_publishing ) )
            throw new UnacceptableValueException( "The value $uses_scheduled_publishing must be a boolean" );
    
        if( !$uses_scheduled_publishing ) // unset
        {
            $this->getProperty()->usesScheduledPublishing = false;
            $this->getProperty()->timeToPublish           = NULL;
            $this->getProperty()->publishIntervalHours    = NULL;
            $this->getProperty()->publishDaysOfWeek       = NULL;
            $this->getProperty()->cronExpression          = NULL;
            $this->getProperty()->sendReportToUsers       = NULL;
            $this->getProperty()->sendReportToGroups      = NULL;
            $this->getProperty()->sendReportOnErrorOnly   = false;
            
            return $this;
        }
        // days are supplied
        else if( $day_of_week != NULL )
        {
            $this->getProperty()->usesScheduledPublishing = true;
        
            // a string
            if( in_array( $day_of_week, $this->days_of_week ) )
            {
                $this->getProperty()->publishDaysOfWeek->dayOfWeek = $day_of_week;
                
                // possible error message from Cascade?
                if( $time_to_publish != NULL )
                {
                    $this->getProperty()->timeToPublish = $time_to_publish;
                }
                else
                {
                    $this->getProperty()->timeToPublish = self::DEFAULT_TIME;
                }
            }
            // an array of strings
            else if( is_array( $day_of_week ) )
            {
                foreach( $day_of_week as $day )
                {
                    if( !in_array( $day, $this->days_of_week ) )
                    {
                        throw new UnacceptableValueException( "The value $day is not acceptable." );
                    }
                }
            
                $temp = array();
            
                // to preserve order, which does not matter
                foreach( $this->days_of_week as $day ) 
                {
                    if( in_array( $day, $day_of_week ) )
                    {
                        $temp[] = $day;
                    }
                }
                
                $this->getProperty()->publishDaysOfWeek->dayOfWeek = $temp;
                // possible error message from Cascade: yes
                if( $time_to_publish != NULL )
                {
                    $this->getProperty()->timeToPublish = $time_to_publish;
                }
                else
                {
                    $this->getProperty()->timeToPublish = self::DEFAULT_TIME;
                }
            }
            else
            {
                throw new UnacceptableValueException( "The value $day_of_week is not acceptable." );
            }
            unset( $this->getProperty()->publishIntervalHours );
            unset( $this->getProperty()->cronExpression );
        }
        // interval is supplied
        else if( $publish_interval_hours != NULL ) 
        {
            $this->getProperty()->usesScheduledPublishing = true;

            if( intval( $publish_interval_hours ) > 0 &&
                intval( $publish_interval_hours ) < 24 )
            {
                $this->getProperty()->publishIntervalHours = $publish_interval_hours;
            }
            else
            {
                throw new UnacceptableValueException( "The value $publish_interval_hours is not acceptable." );
            }
            
            unset( $this->getProperty()->publishDaysOfWeek );
            unset( $this->getProperty()->cronExpression );
            // possible error message from Cascade?
            $this->getProperty()->timeToPublish     = $time_to_publish;
        }
        // a cron expression is supplied
        else if( $cron_expression != NULL )
        {
            $this->getProperty()->usesScheduledPublishing = true;

            unset( $this->getProperty()->timeToPublish );
            unset( $this->getProperty()->publishIntervalHours );
            unset( $this->getProperty()->publishDaysOfWeek );
            $this->getProperty()->cronExpression = $cron_expression;
        }
        else
        {
            throw new EmptyValueException( "No input values are supplied." );
        }
        
        return $this;
    }
    
    public function setSendReportOnErrorOnly( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean" );
        $this->getProperty()->sendReportOnErrorOnly   = $bool;
        return $this;
    }
    
    public function unsetScheduledPublishing()
    {
        return $this->setScheduledPublishing( false );
    }

    private $days_of_week;
}
?>