<?php namespace App\Events;

use Illuminate\Queue\SerializesModels;
use models\summit\SummitAttendee;

/**
 * Class MyScheduleAdd
 * @package App\Events
 */
class MyScheduleAdd extends Event
{

    use SerializesModels;

    /**
     * @var SummitAttendee
     */
    protected $attendee;

    /**
     * @var int
     */
    protected $event_id;

    /**
     * MyScheduleAdd constructor.
     * @param SummitAttendee $attendee
     * @param int $event_id
     */
    function __construct(SummitAttendee $attendee, $event_id)
    {
        $this->attendee = $attendee;
        $this->event_id = $event_id;
    }

    /**
     * @return SummitAttendee
     */
    public function getAttendee(){ return $this->attendee;}

    /**
     * @return int
     */
    public function getEventId(){ return $this->event_id;}
}