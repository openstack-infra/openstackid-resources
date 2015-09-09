<?php namespace App\Http\Controllers;

use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use Request;

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
     * @param ISummitRepository $repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct(ISummitRepository $repository, IResourceServerContext $resource_server_context)
    {
        parent::__construct($resource_server_context);
        $this->repository = $repository;
    }

    public function getSummit($summit_id)
    {
        $expand = Request::input('expand', '');
        $summit_id = intval($summit_id);
        try {
            $summit = $this->repository->getById($summit_id);

            if (is_null($summit))
            {
                $this->error404();
            }

            $data = $summit->toArray();

            if (!empty($expand))
            {
                $expand = explode(',', $expand);
                foreach ($expand as $relation) {
                    switch (trim($relation)) {
                        case 'locations': {
                            $locations = array();
                            foreach ($summit->locations() as $location) {
                                array_push($locations, $location->toArray());
                            }
                            $data['locations'] = $locations;
                        }
                            break;
                        case 'sponsors': {
                            $sponsors = array();
                            foreach ($summit->sponsors() as $company) {
                                array_push($sponsors, $company->toArray());
                            }
                            $data['sponsors'] = $sponsors;
                        }
                            break;
                        case 'schedule': {

                            $speakers = array();
                            foreach($summit->speakers() as $speaker)
                            {
                                array_push($speakers, $speaker->toArray());
                            }
                            $data['speakers'] = $speakers;

                            $schedule = array();
                            foreach ($summit->schedule() as $event)
                            {
                                array_push($schedule, $event->toArray());
                            }
                            $data['schedule'] = $schedule;

                        }
                        break;
                        case 'summit_types': {
                            $summit_types = array();
                            foreach ($summit->summit_types() as $type) {
                                array_push($summit_types, $type->toArray());
                            }
                            $data['summit_types'] = $summit_types;
                        }
                            break;
                        case 'event_types': {
                            $event_types = array();
                            foreach ($summit->event_types() as $event_type) {
                                array_push($event_types, $event_type->toArray());
                            }
                            $data['event_types'] = $event_types;
                        }
                            break;
                        case 'presentation_categories': {
                            $presentation_categories = array();
                            foreach ($summit->presentation_categories() as $cat) {
                                array_push($presentation_categories, $cat->toArray());
                            }
                            $data['presentation_categories'] = $presentation_categories;

                        }
                            break;
                    }
                }
            }
            return $this->ok($data);
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}