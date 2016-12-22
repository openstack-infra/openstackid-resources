<?php namespace App\Console\Commands;
/**
 * Copyright 2016 OpenStack Foundation
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
use services\model\IChatTeamService;
/**
 * Class ChatTeamMessagesSender
 * @package App\Console\Commands
 */
final class ChatTeamMessagesSender extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'teams:message-sender';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:message-sender {bach_size?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out Teams Chat Messages';

    /**
     * @var IChatTeamService
     */
    private $service;

    /**
     * ChatTeamMessagesSender constructor.
     * @param IChatTeamService $service
     */
    public function __construct(IChatTeamService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bach_size = $this->argument('bach_size');
        if(is_null($bach_size)) $bach_size = 100;
        $res = $this->service->sendMessages($bach_size);
        $this->info(sprintf("total messages sent %s", $res));
    }
}