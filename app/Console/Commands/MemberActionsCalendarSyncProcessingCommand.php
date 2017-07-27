<?php namespace App\Console\Commands;
/**
 * Copyright 2017 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use Illuminate\Console\Command;
use services\model\IMemberActionsCalendarSyncProcessingService;

/**
 * Class MemberActionsCalendarSyncProcessingCommand
 * @package App\Console\Commands
 */
final class MemberActionsCalendarSyncProcessingCommand extends Command
{
    /**
     * @var IMemberActionsCalendarSyncProcessingService
     */
    private $service;

    /**
     * MemberActionsCalendarSyncProcessingCommand constructor.
     * @param IMemberActionsCalendarSyncProcessingService $service
     */
    public function __construct(IMemberActionsCalendarSyncProcessingService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'summit:member-schedule-action-process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Member External Schedule Sync Actions';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summit:member-schedule-action-process {batch_size?}';

    public function handle()
    {
        $batch_size = $this->argument('batch_size');
        if(empty($batch_size))
            $batch_size = 100;

        $start  = time();

        $this->info(sprintf("processing batch size of %s", $batch_size));


        $res = $this->service->processActions($batch_size);

        $end   = time();
        $delta = $end - $start;
        $this->info(sprintf("execution call %s seconds - processed entries %s", $delta, $res));

    }
}