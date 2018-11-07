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
use App\Services\Model\IMemberActionsCalendarSyncProcessingService;
use models\summit\CalendarSync\CalendarSyncInfo;
use Illuminate\Support\Facades\Log;
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
    protected $signature = 'summit:member-schedule-action-process {provider} {batch_size?}';

    public function handle()
    {
        $batch_size = $this->argument('batch_size');
        $provider   = $this->argument('provider');
        if(!CalendarSyncInfo::isValidProvider($provider)){
            $this->error("provider param is not valid , valid values are [Google, Outlook, iCloud]");
            log::error("provider param is not valid , valid values are [Google, Outlook, iCloud]");
            return false;
        }
        if(empty($batch_size))
            $batch_size = 1000;

        $start  = time();

        $this->info(sprintf("processing provider %s - batch size of %s", $provider, $batch_size));
        log::info(sprintf("processing provider %s - batch size of %s", $provider, $batch_size));

        $res = $this->service->processActions($provider, $batch_size);

        $end   = time();
        $delta = $end - $start;
        $this->info(sprintf("execution call %s seconds - processed entries %s", $delta, $res));
        log::info(sprintf("execution call %s seconds - processed entries %s", $delta, $res));
    }
}