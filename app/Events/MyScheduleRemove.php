<?php namespace App\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class MyScheduleRemove
 * @package App\Events
 */
class MyScheduleRemove extends MyScheduleAdd
{
    use SerializesModels;
}
