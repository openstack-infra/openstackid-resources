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
use App\Services\Model\IAdminActionsCalendarSyncProcessingService;
/**
 * Class AdminActionsCalendarSyncProcessingCommand
 * @package App\Console\Commands
 */
final class AdminActionsCalendarSyncProcessingCommand extends Command
{
    /**
     * @var IAdminActionsCalendarSyncProcessingService
     */
    private $service;

    /**
     * MemberActionsCalendarSyncProcessingCommand constructor.
     * @param IAdminActionsCalendarSyncProcessingService $service
     */
    public function __construct(IAdminActionsCalendarSyncProcessingService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'summit:admin-schedule-action-process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Admin External Schedule Sync Actions';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summit:admin-schedule-action-process {batch_size?}';

    public function handle()
    {
        $batch_size = $this->argument('batch_size');
        if(empty($batch_size))
            $batch_size = PHP_INT_MAX;

        $start  = time();

        $this->info(sprintf("processing batch size of %s", $batch_size));


        $res = $this->service->processActions($batch_size);

        $end   = time();
        $delta = $end - $start;
        $this->info(sprintf("execution call %s seconds - processed entries %s", $delta, $res));

    }
}