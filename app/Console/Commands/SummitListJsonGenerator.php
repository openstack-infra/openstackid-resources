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

use Illuminate\Support\Facades\Log;
use libs\utils\ICacheService;
use Mockery\Exception;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\PagingResponse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

/**
 * Class SummitListJsonGenerator
 * @package App\Console\Commands
 */
final class SummitListJsonGenerator extends Command {

    /**
     * @var ISummitService
     */
    private $service;

    /**
     * @var ISummitRepository
     */
    private $repository;

    /**
     * @var ICacheService
     */
    private $cache_service;

    /**
     * SummitJsonGenerator constructor.
     * @param ISummitRepository $repository
     * @param ISummitService $service
     * @param ICacheService $cache_service
     */
    public function __construct(
        ISummitRepository $repository,
        ISummitService $service,
        ICacheService $cache_service
    )
    {
        parent::__construct();
        $this->repository    = $repository;
        $this->service       = $service;
        $this->cache_service = $cache_service;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'summit-list:json-generator';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summit-list:json-generator';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates Summit List Json';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        try {

            $this->info("processing summits");
            $start  = time();

            $summits = [];
            $expand  = '';
            foreach($this->repository->getAvailables() as $summit){
                $summits[] = SerializerRegistry::getInstance()->getSerializer($summit)->serialize($expand);
            }

            $response = new PagingResponse
            (
                count($summits),
                count($summits),
                1,
                1,
                $summits
            );

            $data           = $response->toArray();
            $key_id         = "/api/v1/summits";
            $key_id_public  = "/api/public/v1/summits";

            $cache_lifetime = intval(Config::get('server.response_cache_lifetime', 300));
            $current_time   = time();
            Log::info(sprintf("writing cached response for %s", $key_id));
            $this->info(sprintf("writing cached response for %s", $key_id));
            $this->cache_service->setSingleValue($key_id, gzdeflate(json_encode($data), 9), $cache_lifetime);
            $this->cache_service->setSingleValue($key_id.".generated", $current_time, $cache_lifetime);
            Log::info(sprintf("writing cached response for %s", $key_id_public));
            $this->info(sprintf("writing cached response for %s", $key_id_public));
            $this->cache_service->setSingleValue($key_id_public, gzdeflate(json_encode($data), 9), $cache_lifetime);
            $this->cache_service->setSingleValue($key_id_public.".generated", $current_time, $cache_lifetime);
            $end   = time();
            $delta = $end - $start;
            $this->info(sprintf("execution call %s seconds", $delta));
        }
        catch (Exception $ex) {
            Log::error($ex);
        }
    }

}
