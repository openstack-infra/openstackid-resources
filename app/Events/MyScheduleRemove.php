<?php namespace App\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class MyScheduleRemove
 * @package App\Events
 */
class MyScheduleRemove extends MyScheduleAdd
{

    use SerializesModels;

    /**
     * MyScheduleAdd constructor.
     * @param SummitAttendee $attendee
     * @param int $event_id
     */
    function __construct(SummitAttendee $attendee, $event_id)
    {
        parent::__construct($attendee, $event_id);
    }



}
