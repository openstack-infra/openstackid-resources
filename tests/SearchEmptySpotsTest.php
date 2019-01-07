<?php
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
use Illuminate\Support\Facades\App;
use LaravelDoctrine\ORM\Facades\EntityManager;
use App\Models\Utils\IntervalParser;
use services\model\ISummitService;
use utils\FilterParser;
use Tests\TestCase;
/**
 * Class SearchEmptySpotsTest
 */
final class SearchEmptySpotsTest extends TestCase
{

    public function testIntervalParser2(){
        $summit_repository   = EntityManager::getRepository(\models\summit\Summit::class);
        $summit              = $summit_repository->getById(23);
        $summit_time_zone    = $summit->getTimeZone();
        $start_datetime      = new DateTime( "2017-11-04 07:00:00", $summit_time_zone);
        $end_datetime        = new DateTime("2017-11-05 18:00:00", $summit_time_zone);

        $intervals           = IntervalParser::getInterval($start_datetime, $end_datetime);

        $this->assertTrue(count($intervals) == 2);
    }

    public function testIntervalParse1(){
        $summit_repository   = EntityManager::getRepository(\models\summit\Summit::class);
        $summit              = $summit_repository->getById(23);
        $summit_time_zone    = $summit->getTimeZone();
        $start_datetime      = new DateTime( "2017-11-04 07:00:00", $summit_time_zone);
        $end_datetime        = new DateTime("2017-11-04 18:00:00", $summit_time_zone);

        $intervals           = IntervalParser::getInterval($start_datetime, $end_datetime);

        $this->assertTrue(count($intervals) == 1);
    }

    public function testIntervalParser3(){
        $summit_repository   = EntityManager::getRepository(\models\summit\Summit::class);
        $summit              = $summit_repository->getById(23);
        $summit_time_zone    = $summit->getTimeZone();
        $start_datetime      = new DateTime( "2017-11-04 07:00:00", $summit_time_zone);
        $end_datetime        = new DateTime("2017-11-06 18:00:00", $summit_time_zone);

        $intervals           = IntervalParser::getInterval($start_datetime, $end_datetime);

        $this->assertTrue(count($intervals) == 3);
    }

    public function testFindSpots(){

        $summit_repository   = EntityManager::getRepository(\models\summit\Summit::class);
        $summit              = $summit_repository->getById(23);
        $summit_time_zone    = $summit->getTimeZone();
        $start_datetime      = new DateTime( "2017-11-04 07:00:00", $summit_time_zone);
        $end_datetime        = new DateTime("2017-11-05 18:00:00", $summit_time_zone);
        $start_datetime_unix = $start_datetime->getTimestamp();
        $end_datetime_unix   = $end_datetime->getTimestamp();

        $service             = App::make(\services\model\ISummitService::class);
        if(!$service instanceof ISummitService )
            return ;

        $filter = FilterParser::parse
        (
            [
                'location_id==318,location_id==320',
                'start_date>='.$start_datetime_unix,
                'end_date<='.$end_datetime_unix,
                'gap==10',
            ],
            [
                'location_id' => ['=='],
                'start_date'  => ['>='],
                'end_date'    => ['<='],
                'gap'         => ['>', '<', '<=', '>=', '=='],
            ]
        );

        $gaps = $service->getSummitScheduleEmptySpots($summit, $filter);

        $this->assertTrue(count($gaps) > 0);
    }
}