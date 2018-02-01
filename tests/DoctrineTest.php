<?php

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
use models\summit\SummitExternalLocation;
use utils\PagingInfo;
use utils\FilterParser;
use utils\PagingResponse;
use ModelSerializers\SerializerRegistry;
/**
 * Class DoctrineTest
 */
final class DoctrineTest extends TestCase
{
    public function testGeSummitById()
    {
        $repo   =  EntityManager::getRepository(\models\summit\Summit::class);
        $summit = $repo->getById(6);
        $locations = $summit->getLocations();
        $locations = $locations->toArray();
        $this->assertTrue($summit->getIdentifier() === 6);
        $this->assertTrue(count($locations) > 0);
        $data = SerializerRegistry::getInstance()->getSerializer($summit)->serialize();
        $this->assertTrue(is_array($data));
    }

    public function testGetSummitVenues(){

        $repo   =  EntityManager::getRepository(\models\summit\Summit::class);
        $summit = $repo->getById(6);
        $venues = $summit->getVenues();
        foreach($venues->toArray() as $venue)
        {
            foreach($venue->getRooms() as $r)
            {

            }
            foreach($venue->getFloors() as $f)
            {

            }
        }
    }

    public function testGetAttendeeById(){
        $repo    =  EntityManager::getRepository(\models\summit\Summit::class);
        $summit  = $repo->getById(6);
        $this->assertTrue(!is_null($summit));
        $attendee = $summit->getAttendeeById(493);
        $this->assertTrue(!is_null($attendee));

        $member = $attendee->getMember();
        $this->assertTrue(!is_null($member));
        $feedback = $attendee->getEmittedFeedback();
        $schedule = $attendee->getSchedule();
    }

    public function testGetMember(){
        $em = Registry::getManager('ss');
        $repo   =  $em->getRepository(\models\main\Member::class);
        $me = $repo->find(11624);
        $this->assertTrue(!is_null($me));
        $photo = $me->getPhoto();
        $filename = $photo->getFilename();
        $this->assertTrue(!is_null($photo));
    }

    public function testGetEventFeedback(){
        $repo    =  EntityManager::getRepository(\models\summit\Summit::class);
        $summit  = $repo->getById(6);
        $this->assertTrue(!is_null($summit));
        $event = $summit->getEvent(9454);
        $this->assertTrue(!is_null($event));
    }

    public function testGetFile(){
        $em = Registry::getManager('ss');
        $repo   =  $em->getRepository(\models\main\File::class);
        $file = $repo->find(1);
        $this->assertTrue(!is_null($file));
    }

    public function testAddLocation()
    {
        $repo   =  EntityManager::getRepository(\models\summit\Summit::class);
        $summit = $repo->getById(6);
        $newExternalLocation = new SummitExternalLocation();
        $newExternalLocation->setType(SummitExternalLocation::Lounge);
        $newExternalLocation->setSummit($summit);
        $summit->getLocations()->add($newExternalLocation);
        $em = Registry::getManager('ss');
        $em->flush();
    }

    public function testGetEvents(){

        $filter = FilterParser::parse('tags=@nova', array
        (
            'title'          => array('=@', '=='),
            'tags'           => array('=@', '=='),
            'start_date'     => array('>', '<', '<=', '>=', '=='),
            'end_date'       => array('>', '<', '<=', '>=', '=='),
            'summit_type_id' => array('=='),
            'event_type_id'  => array('=='),
        ));

        $repo   =  EntityManager::getRepository(\models\summit\Summit::class);
        $summit = $repo->getById(6);
        $response = $repo->getEvents($summit->getIdentifier(), new PagingInfo(1, 10), $published = true, $filter);
        $schedule = $response->getItems();
        $this->assertTrue(count($schedule) > 0);
        $event = $schedule[0];
        $tags = $event->getTags()->toArray();
        $this->assertTrue(count($tags) > 0);
    }

    public function testGetPresentation()
    {
        $repo   =  EntityManager::getRepository(\models\summit\Summit::class);
        $summit = $repo->getById(6);
        $presentation = $summit->getEvent(6859);
        $videos = $presentation->getVideos();
        $slides = $presentation->getSlides();
        $links  = $presentation->getLinks();
        $this->assertTrue(!is_null($presentation));
    }

    public function testGetPresentations()
    {
        $repo   =  EntityManager::getRepository(\models\summit\Summit::class);
        $summit = $repo->getById(6);
        $presentations = $summit->getPresentations();
    }


    public function testGetSpeakers(){
        $repo     = EntityManager::getRepository(\models\summit\Summit::class);
        $summit   = $repo->getById(6);
        $speakers = $summit->getSpeakers();
        $sponsors = $summit->getSponsors();
        $repo     = EntityManager::getRepository(\models\summit\PresentationSpeaker::class);
        $speakers = $repo->getSpeakersBySummit($summit, new PagingInfo(1,10))->getItems();
        $this->assertTrue(count($speakers) > 0);
        $speaker = $speakers[0];
        $member = $speaker->getMember();
        $id = $member->getId();
    }

    public function testGetSpeakerPublishedRegularPresentations($speaker_id = 1759){
        $repo1     = EntityManager::getRepository(\models\summit\PresentationSpeaker::class);
        $repo2     = EntityManager::getRepository(\models\summit\Summit::class);
        $summit    = $repo2->getById(23);
        $speaker   = $repo1->getById($speaker_id);

        $this->assertTrue($speaker->hasPublishedRegularPresentations($summit));

        $presentations = $speaker->getPublishedRegularPresentations($summit);

        $this->assertTrue(count($presentations) > 0);
    }

    public function testGetSpeakerAlternatePresentations($speaker_id = 70){
        $repo1     = EntityManager::getRepository(\models\summit\PresentationSpeaker::class);
        $repo2     = EntityManager::getRepository(\models\summit\Summit::class);
        $summit    = $repo2->getById(23);
        $speaker   = $repo1->getById($speaker_id);

        $this->assertTrue($speaker->hasAlternatePresentations($summit));

        $presentations = $speaker->getAlternatePresentations($summit);

        $this->assertTrue(count($presentations) > 0);
    }

    public function testGetSpeakerRejectedPresentations($speaker_id = 70){
        $repo1     = EntityManager::getRepository(\models\summit\PresentationSpeaker::class);
        $repo2     = EntityManager::getRepository(\models\summit\Summit::class);
        $summit    = $repo2->getById(23);
        $speaker   = $repo1->getById($speaker_id);

        $this->assertTrue($speaker->hasRejectedPresentations($summit));

        $presentations = $speaker->getRejectedPresentations($summit);

        $this->assertTrue(count($presentations) > 0);
    }
}