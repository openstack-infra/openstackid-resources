<?php namespace App\Http\Controllers;

use models\summit\ISummitRepository;
use models\oauth2\IResourceServerContext;
use Request;
use models\summit\SummitAirport;

/**
 * Copyright 2015 OpenStack Foundation
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
class OAuth2SummitApiController extends OAuth2ProtectedController
{

    /**
     * @param ISummitRepository  $repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct(ISummitRepository $repository, IResourceServerContext  $resource_server_context)
    {
        parent::__construct($resource_server_context);
        $this->repository = $repository;
    }

    public function getSummit($summit_id)
    {
        $expand = Request::input('expand', '');
        $summit_id = intval($summit_id);
        try
        {
            $summit = $this->repository->getById($summit_id);
            $data   = $summit->toArray();
            if(!empty($expand))
            {
                $expand = explode(',',$expand);
                foreach($expand as $relation)
                {
                    switch(trim($relation))
                    {
                        case 'locations':
                        {
                            $locations =  array();
                            foreach($summit->locations() as $location)
                            {
                                array_push($locations, $location->toArray());
                            }
                            $data['locations'] = $locations;
                        }
                        break;
                        case 'sponsors':
                        {

                            $data['sponsors'] = array();
                        }
                        break;
                        case 'schedule':
                        {
                            $schedule =  array();
                            foreach($summit->schedule() as $event)
                            {
                                array_push($schedule, $event->toArray());
                            }
                            $data['schedule'] = $schedule;
                        }
                        break;
                        case 'summit_types':
                        {

                            $data['summit_types'] = array();
                        }
                            break;
                        case 'event_types':
                        {

                            $data['event_types'] = array();
                        }
                        break;
                        case 'presentation_categories':
                        {

                            $data['presentation_categories'] = array();
                        }
                        break;
                    }
                }
            }
            return ($data)? $this->ok($data) : $this->error404();
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}