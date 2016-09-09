<?php namespace ModelSerializers;
use Libs\ModelSerializers\IModelSerializer;
use ModelSerializers\Locations\SummitAirportSerializer;
use ModelSerializers\Locations\SummitExternalLocationSerializer;
use ModelSerializers\Locations\SummitHotelSerializer;
use ModelSerializers\Locations\SummitLocationImageSerializer;
use ModelSerializers\Locations\SummitVenueFloorSerializer;
use ModelSerializers\Locations\SummitVenueRoomSerializer;
use ModelSerializers\Locations\SummitVenueSerializer;

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
final class SerializerRegistry
{
    /**
     * @var SerializerRegistry
     */
    private static $instance;


    private function __clone()
    {
    }

    /**
     * @return SerializerRegistry
     */
    public static function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new SerializerRegistry();
        }

        return self::$instance;
    }

    private $registry = array();

    private function __construct()
    {
        $this->registry['Summit']                     = SummitSerializer::class;
        $this->registry['SummitType']                 = SummitTypeSerializer::class;
        $this->registry['SummitEventType']            = SummitEventTypeSerializer::class;
        $this->registry['SummitTicketType']           = SummitTicketTypeSerializer::class;
        $this->registry['PresentationCategory']       = PresentationCategorySerializer::class;
        $this->registry['PresentationCategoryGroup']  = PresentationCategoryGroupSerializer::class;
        $this->registry['Tag']                        = TagSerializer::class;
        $this->registry['SummitEvent']                = SummitEventSerializer::class;
        $this->registry['SummitEventMetricsSnapshot'] = SummitEventMetricsSnapshotSerializer::class;
        $this->registry['Presentation']               = PresentationSerializer::class;
        $this->registry['PresentationVideo']          = PresentationVideoSerializer::class;
        $this->registry['PresentationSlide']          = PresentationSlideSerializer::class;
        $this->registry['PresentationLink']           = PresentationLinkSerializer::class;
        $this->registry['Company']                    = CompanySerializer::class;
        $this->registry['PresentationSpeaker']        = PresentationSpeakerSerializer::class;
        $this->registry['SummitEventFeedback']        = SummitEventFeedbackSerializer::class;
        $this->registry['SummitAttendee']             = SummitAttendeeSerializer::class;
        $this->registry['SummitAttendeeSchedule']     = SummitAttendeeScheduleSerializer::class;
        $this->registry['SummitEntityEvent']          = SummitEntityEventSerializer::class;

        // locations
        $this->registry['SummitVenue']                = SummitVenueSerializer::class;
        $this->registry['SummitVenueRoom']            = SummitVenueRoomSerializer::class;
        $this->registry['SummitVenueFloor']           = SummitVenueFloorSerializer::class;
        $this->registry['SummitExternalLocation']     = SummitExternalLocationSerializer::class;
        $this->registry['SummitHotel']                = SummitHotelSerializer::class;
        $this->registry['SummitAirport']              = SummitAirportSerializer::class;
        $this->registry['SummitLocationImage']        = SummitLocationImageSerializer::class;

        // member
        $this->registry['Member']                    = MemberSerializer::class;

        $this->registry['Group']                     = GroupSerializer::class;

        // push notification
        $this->registry['SummitPushNotification']    = SummitPushNotificationSerializer::class;
    }

    /**
     * @param object $object
     * @return IModelSerializer
     */
    public function getSerializer($object){
        $reflect = new \ReflectionClass($object);
        $class   = $reflect->getShortName();
        if(!isset($this->registry[$class]))
            throw new \InvalidArgumentException('Serializer not found for '.$class);

        $serializer_class = $this->registry[$class];
        return new $serializer_class($object);
    }
}