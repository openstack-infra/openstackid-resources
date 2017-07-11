<?php namespace App\Events;

use models\main\Member;
use models\summit\Summit;

/**
 * Class MyScheduleAdd
 * @package App\Events
 */
class MyScheduleAdd extends SummitEventAction
{

    /**
     * @var Member
     */
    protected $member;

    /**
     * @var Summit
     */
    protected $summit;


    /**
     * MyScheduleAdd constructor.
     * @param Member $member
     * @param Summit $summit
     * @param int $event_id
     */
    public function __construct($member, $summit, $event_id){

        $this->member = $member;
        $this->summit = $summit;
        parent::__construct($event_id);
    }

    public function getMember(){ return $this->member; }

    public function getSummit(){ return $this->summit;}
}