<?php namespace ModelSerializers;
use Libs\ModelSerializers\IModelSerializer;
use models\main\ChatTeam;
use models\main\ChatTeamMember;
use models\main\ChatTeamPushNotificationMessage;
use ModelSerializers\ChatTeams\ChatTeamInvitationSerializer;
use ModelSerializers\ChatTeams\ChatTeamMemberSerializer;
use ModelSerializers\ChatTeams\ChatTeamPushNotificationMessageSerializer;
use ModelSerializers\ChatTeams\ChatTeamSerializer;
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

    const SerializerType_Public  = 'PUBLIC';
    const SerializerType_Private = 'PRIVATE';

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
        $this->registry['SummitWIFIConnection']       = SummitWIFIConnectionSerializer::class;
        $this->registry['SummitType']                 = SummitTypeSerializer::class;
        $this->registry['SummitEventType']            = SummitEventTypeSerializer::class;
        $this->registry['SummitTicketType']           = SummitTicketTypeSerializer::class;
        $this->registry['PresentationCategory']       = PresentationCategorySerializer::class;
        $this->registry['PresentationCategoryGroup']  = PresentationCategoryGroupSerializer::class;
        $this->registry['Tag']                        = TagSerializer::class;
        $this->registry['SummitEvent']                = SummitEventSerializer::class;
        $this->registry['SummitGroupEvent']           = SummitGroupEventSerializer::class;
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
        $this->registry['SummitEventWithFile']        = SummitEventWithFileSerializer::class;

        // locations
        $this->registry['SummitVenue']                = SummitVenueSerializer::class;
        $this->registry['SummitVenueRoom']            = SummitVenueRoomSerializer::class;
        $this->registry['SummitVenueFloor']           = SummitVenueFloorSerializer::class;
        $this->registry['SummitExternalLocation']     = SummitExternalLocationSerializer::class;
        $this->registry['SummitHotel']                = SummitHotelSerializer::class;
        $this->registry['SummitAirport']              = SummitAirportSerializer::class;
        $this->registry['SummitLocationImage']        = SummitLocationImageSerializer::class;

        // member
        $this->registry['Member']                          = [
            self::SerializerType_Public  => PublicMemberSerializer::class,
            self::SerializerType_Private => OwnMemberSerializer::class
        ];

        $this->registry['Group']                           = GroupSerializer::class;
        $this->registry['Affiliation']                     = AffiliationSerializer::class;
        $this->registry['Organization']                    = OrganizationSerializer::class;

        // push notification
        $this->registry['SummitPushNotification']          = SummitPushNotificationSerializer::class;

        // teams
        $this->registry['ChatTeam']                        = ChatTeamSerializer::class;
        $this->registry['ChatTeamMember']                  = ChatTeamMemberSerializer::class;
        $this->registry['ChatTeamInvitation']              = ChatTeamInvitationSerializer::class;
        $this->registry['ChatTeamPushNotificationMessage'] = ChatTeamPushNotificationMessageSerializer::class;
    }

    /**
     * @param object $object
     * @param string $type
     * @return IModelSerializer
     */
    public function getSerializer($object, $type = self::SerializerType_Public){
        $reflect = new \ReflectionClass($object);
        $class   = $reflect->getShortName();
        if(!isset($this->registry[$class]))
            throw new \InvalidArgumentException('Serializer not found for '.$class);

        $serializer_class = $this->registry[$class];

        if(is_array($serializer_class)){
            if(!isset($serializer_class[$type]))
                throw new \InvalidArgumentException(sprintf('Serializer not found for %s , type %s', $class, $type));
            $serializer_class = $serializer_class[$type];
        }


        return new $serializer_class($object);
    }
}