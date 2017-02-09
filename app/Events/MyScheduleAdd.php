<?php namespace App\Events;

use models\summit\SummitAttendee;

/**
 * Class MyScheduleAdd
 * @package App\Events
 */
class MyScheduleAdd extends SummitEventAction
{

    /**
     * @var SummitAttendee
     */
    protected $attendee;

    /**
     * MyScheduleAdd constructor.
     * @param SummitAttendee $attendee
     * @param int $event_id
     */
    function __construct(SummitAttendee $attendee, $event_id)
    {
        $this->attendee = $attendee;
        parent::__construct($event_id);
    }

    /**
     * @return SummitAttendee
     */
    public function getAttendee(){ return $this->attendee;}
}