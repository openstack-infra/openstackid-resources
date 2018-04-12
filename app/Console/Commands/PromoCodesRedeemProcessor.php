<?php namespace App\Console\Commands;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Services\Model\IAttendeeService;
use Illuminate\Console\Command;
use models\summit\ISummitRepository;

/**
 * Class PromoCodesRedeemProcessor
 * @package App\Console\Commands
 */
final class PromoCodesRedeemProcessor extends Command
{
    /**
     * @var IAttendeeService
     */
    private $service;

    /**
     * @var ISummitRepository
     */
    private $repository;


    /**
     * PromoCodesRedeemProcessor constructor.
     * @param IAttendeeService $service
     * @param ISummitRepository $repository
     */
    public function __construct
    (
        IAttendeeService $service,
        ISummitRepository $repository
    )
    {
        parent::__construct();
        $this->repository = $repository;
        $this->service    = $service;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'summit:promo-codes-redeem-processor';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summit:promo-codes-redeem-processor {summit_id?}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Redeems promo codes per summit';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $summit_id = $this->argument('summit_id');

        if(is_null($summit_id))// if we dont provide a summit id, then get current
            $summit = $this->repository->getCurrent();
        else
            $summit = $this->repository->getById(intval($summit_id));
        $this->info(sprintf("starting to process promo codes for summit id %s", $summit->getIdentifier()));
        $has_more_items = false;
        $page           = 1;

        do{
            $this->info(sprintf("processing page %s for summit id %s", $page, $summit->getIdentifier()));
            $has_more_items = $this->service->updateRedeemedPromoCodes($summit, $page);
            ++$page;
        } while($has_more_items);

        $this->info(sprintf("finished to process promo codes for summit id %s", $summit->getIdentifier()));
    }
}