<?php namespace ModelSerializers;
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
use App\ModelSerializers\CCLA\TeamSerializer;
use App\ModelSerializers\Marketplace\CloudServiceOfferedSerializer;
use App\ModelSerializers\Marketplace\ConfigurationManagementTypeSerializer;
use App\ModelSerializers\Marketplace\ConsultantClientSerializer;
use App\ModelSerializers\Marketplace\ConsultantSerializer;
use App\ModelSerializers\Marketplace\ConsultantServiceOfferedTypeSerializer;
use App\ModelSerializers\Marketplace\DataCenterLocationSerializer;
use App\ModelSerializers\Marketplace\DataCenterRegionSerializer;
use App\ModelSerializers\Marketplace\DistributionSerializer;
use App\ModelSerializers\Marketplace\GuestOSTypeSerializer;
use App\ModelSerializers\Marketplace\HyperVisorTypeSerializer;
use App\ModelSerializers\Marketplace\MarketPlaceReviewSerializer;
use App\ModelSerializers\Marketplace\OfficeSerializer;
use App\ModelSerializers\Marketplace\OpenStackImplementationApiCoverageSerializer;
use App\ModelSerializers\Marketplace\PricingSchemaTypeSerializer;
use App\ModelSerializers\Marketplace\PrivateCloudServiceSerializer;
use App\ModelSerializers\Marketplace\PublicCloudServiceSerializer;
use App\ModelSerializers\Marketplace\RegionalSupportSerializer;
use App\ModelSerializers\Marketplace\RegionSerializer;
use App\ModelSerializers\Marketplace\RemoteCloudServiceSerializer;
use App\ModelSerializers\Marketplace\ServiceOfferedTypeSerializer;
use App\ModelSerializers\Marketplace\SpokenLanguageSerializer;
use App\ModelSerializers\Marketplace\SupportChannelTypeSerializer;
use App\ModelSerializers\PushNotificationMessageSerializer;
use App\ModelSerializers\Software\OpenStackComponentSerializer;
use App\ModelSerializers\Software\OpenStackReleaseSerializer;
use App\ModelSerializers\Summit\AdminSummitSerializer;
use App\ModelSerializers\Summit\RSVP\Templates\RSVPDropDownQuestionTemplateSerializer;
use App\ModelSerializers\Summit\RSVP\Templates\RSVPLiteralContentQuestionTemplateSerializer;
use App\ModelSerializers\Summit\RSVP\Templates\RSVPMultiValueQuestionTemplateSerializer;
use App\ModelSerializers\Summit\RSVP\Templates\RSVPQuestionValueTemplateSerializer;
use App\ModelSerializers\Summit\RSVP\Templates\RSVPSingleValueTemplateQuestionSerializer;
use App\ModelSerializers\Summit\RSVPTemplateSerializer;
use App\ModelSerializers\Summit\ScheduledSummitLocationBannerSerializer;
use App\ModelSerializers\Summit\SelectionPlanSerializer;
use App\ModelSerializers\Summit\SummitLocationBannerSerializer;
use Libs\ModelSerializers\IModelSerializer;
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
use App\ModelSerializers\Marketplace\ApplianceSerializer;
/**
 * Class SerializerRegistry
 * @package ModelSerializers
 */
final class SerializerRegistry
{
    /**
     * @var SerializerRegistry
     */
    private static $instance;

    const SerializerType_Public  = 'PUBLIC';
    const SerializerType_Private = 'PRIVATE';

    private function __clone(){}

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
        $this->registry['Summit']        =
            [
                self::SerializerType_Public  =>  SummitSerializer::class,
                self::SerializerType_Private =>  AdminSummitSerializer::class
            ];

        $this->registry['SelectionPlan']              = SelectionPlanSerializer::class;
        $this->registry['SummitWIFIConnection']       = SummitWIFIConnectionSerializer::class;
        $this->registry['SummitType']                 = SummitTypeSerializer::class;
        $this->registry['SummitEventType']            = SummitEventTypeSerializer::class;
        $this->registry['PresentationType']           = PresentationTypeSerializer::class;
        $this->registry['SummitTicketType']           = SummitTicketTypeSerializer::class;
        $this->registry['PresentationCategory']       = PresentationCategorySerializer::class;
        $this->registry['PresentationCategoryGroup']  = PresentationCategoryGroupSerializer::class;
        $this->registry['PrivatePresentationCategoryGroup'] = PrivatePresentationCategoryGroupSerializer::class;
        $this->registry['Tag']                        = TagSerializer::class;
        $this->registry['SummitEvent']                = SummitEventSerializer::class;
        $this->registry['SummitGroupEvent']           = SummitGroupEventSerializer::class;
        $this->registry['SummitEventMetricsSnapshot'] = SummitEventMetricsSnapshotSerializer::class;
        $this->registry['Presentation']               = PresentationSerializer::class;
        $this->registry['PresentationVideo']          = PresentationVideoSerializer::class;
        $this->registry['PresentationSlide']          = PresentationSlideSerializer::class;
        $this->registry['PresentationLink']           = PresentationLinkSerializer::class;
        $this->registry['Company']                    = CompanySerializer::class;

        $this->registry['PresentationSpeaker']        =
            [
                self::SerializerType_Public  =>  PresentationSpeakerSerializer::class,
                self::SerializerType_Private =>  AdminPresentationSpeakerSerializer::class
            ];

        // RSVP
        $this->registry['RSVP']                       = RSVPSerializer::class;
        $this->registry['RSVPTemplate']               = RSVPTemplateSerializer::class;
        $this->registry['RSVPQuestionValueTemplate']  = RSVPQuestionValueTemplateSerializer::class;

        $this->registry['RSVPSingleValueTemplateQuestion']     = RSVPSingleValueTemplateQuestionSerializer::class;
        $this->registry['RSVPTextBoxQuestionTemplate']         = RSVPSingleValueTemplateQuestionSerializer::class;
        $this->registry['RSVPTextAreaQuestionTemplate']        = RSVPSingleValueTemplateQuestionSerializer::class;
        $this->registry['RSVPLiteralContentQuestionTemplate']  = RSVPLiteralContentQuestionTemplateSerializer::class;
        $this->registry['RSVPMemberEmailQuestionTemplate']     = RSVPSingleValueTemplateQuestionSerializer::class;
        $this->registry['RSVPMemberFirstNameQuestionTemplate'] = RSVPSingleValueTemplateQuestionSerializer::class;
        $this->registry['RSVPMemberLastNameQuestionTemplate']  = RSVPSingleValueTemplateQuestionSerializer::class;
        $this->registry['RSVPMemberLastNameQuestionTemplate']  = RSVPSingleValueTemplateQuestionSerializer::class;

        $this->registry['RSVPCheckBoxListQuestionTemplate']    = RSVPMultiValueQuestionTemplateSerializer::class;
        $this->registry['RSVPRadioButtonListQuestionTemplate'] = RSVPMultiValueQuestionTemplateSerializer::class;
        $this->registry['RSVPDropDownQuestionTemplate']        = RSVPDropDownQuestionTemplateSerializer::class;

        $this->registry['SpeakerExpertise']           = SpeakerExpertiseSerializer::class;
        $this->registry['SpeakerLanguage']            = SpeakerLanguageSerializer::class;
        $this->registry['SpeakerTravelPreference']    = SpeakerTravelPreferenceSerializer::class;
        $this->registry['SpeakerPresentationLink']    = SpeakerPresentationLinkSerializer::class;
        $this->registry['SpeakerActiveInvolvement']   = SpeakerActiveInvolvementSerializer::class;
        $this->registry['SpeakerOrganizationalRole']  = SpeakerOrganizationalRoleSerializer::class;

        $this->registry['SummitEventFeedback']         = SummitEventFeedbackSerializer::class;
        $this->registry['SummitAttendee']              = SummitAttendeeSerializer::class;
        $this->registry['SummitAttendeeTicket']        = SummitAttendeeTicketSerializer::class;
        $this->registry['SummitMemberSchedule']        = SummitMemberScheduleSerializer::class;
        $this->registry['SummitMemberFavorite']        = SummitMemberFavoriteSerializer::class;
        $this->registry['SummitEntityEvent']           = SummitEntityEventSerializer::class;
        $this->registry['SummitEventWithFile']         = SummitEventWithFileSerializer::class;
        $this->registry['SummitScheduleEmptySpot']     = SummitScheduleEmptySpotSerializer::class;
        // promo codes
        $this->registry['SummitRegistrationPromoCode']        = SummitRegistrationPromoCodeSerializer::class;
        $this->registry['MemberSummitRegistrationPromoCode']  = MemberSummitRegistrationPromoCodeSerializer::class;
        $this->registry['SpeakerSummitRegistrationPromoCode'] = SpeakerSummitRegistrationPromoCodeSerializer::class;
        $this->registry['SponsorSummitRegistrationPromoCode'] = SponsorSummitRegistrationPromoCodeSerializer::class;
        $this->registry['PresentationSpeakerSummitAssistanceConfirmationRequest'] = PresentationSpeakerSummitAssistanceConfirmationRequestSerializer::class;
        // locations
        $this->registry['SummitVenue']                   = SummitVenueSerializer::class;
        $this->registry['SummitVenueRoom']               = SummitVenueRoomSerializer::class;
        $this->registry['SummitVenueFloor']              = SummitVenueFloorSerializer::class;
        $this->registry['SummitExternalLocation']        = SummitExternalLocationSerializer::class;
        $this->registry['SummitHotel']                   = SummitHotelSerializer::class;
        $this->registry['SummitAirport']                 = SummitAirportSerializer::class;
        $this->registry['SummitLocationImage']           = SummitLocationImageSerializer::class;
        $this->registry['SummitLocationBanner']          = SummitLocationBannerSerializer::class;
        $this->registry['ScheduledSummitLocationBanner'] = ScheduledSummitLocationBannerSerializer::class;
        // member
        $this->registry['Member']                          = [
            self::SerializerType_Public  => PublicMemberSerializer::class,
            self::SerializerType_Private => OwnMemberSerializer::class
        ];

        $this->registry['Group']                           = GroupSerializer::class;
        $this->registry['Affiliation']                     = AffiliationSerializer::class;
        $this->registry['Organization']                    = OrganizationSerializer::class;
        // push notification
        $this->registry['PushNotificationMessage']         = PushNotificationMessageSerializer::class;
        $this->registry['SummitPushNotification']          = SummitPushNotificationSerializer::class;

        // teams
        $this->registry['ChatTeam']                        = ChatTeamSerializer::class;
        $this->registry['ChatTeamMember']                  = ChatTeamMemberSerializer::class;
        $this->registry['ChatTeamInvitation']              = ChatTeamInvitationSerializer::class;
        $this->registry['ChatTeamPushNotificationMessage'] = ChatTeamPushNotificationMessageSerializer::class;

        // marketplace

        $this->registry['Appliance']                          = ApplianceSerializer::class;
        $this->registry["Distribution"]                       = DistributionSerializer::class;
        $this->registry['MarketPlaceReview']                  = MarketPlaceReviewSerializer::class;
        $this->registry['OpenStackImplementationApiCoverage'] = OpenStackImplementationApiCoverageSerializer::class;
        $this->registry['GuestOSType']                        = GuestOSTypeSerializer::class;
        $this->registry['HyperVisorType']                     = HyperVisorTypeSerializer::class;
        $this->registry['Region']                             = RegionSerializer::class;
        $this->registry['RegionalSupport']                    = RegionalSupportSerializer::class;
        $this->registry['SupportChannelType']                 = SupportChannelTypeSerializer::class;
        $this->registry['Office']                             = OfficeSerializer::class;
        $this->registry['Consultant']                         = ConsultantSerializer::class;
        $this->registry['ConsultantClient']                   = ConsultantClientSerializer::class;
        $this->registry['SpokenLanguage']                     = SpokenLanguageSerializer::class;
        $this->registry['ConfigurationManagementType']        = ConfigurationManagementTypeSerializer::class;
        $this->registry['ServiceOfferedType']                 = ServiceOfferedTypeSerializer::class;
        $this->registry['ConsultantServiceOfferedType']       = ConsultantServiceOfferedTypeSerializer::class;
        $this->registry['DataCenterLocation']                 = DataCenterLocationSerializer::class;
        $this->registry['DataCenterRegion']                   = DataCenterRegionSerializer::class;
        $this->registry['PricingSchemaType']                  = PricingSchemaTypeSerializer::class;
        $this->registry['PrivateCloudService']                = PrivateCloudServiceSerializer::class;
        $this->registry['PublicCloudService']                 = PublicCloudServiceSerializer::class;
        $this->registry['RemoteCloudService']                 = RemoteCloudServiceSerializer::class;
        $this->registry['CloudServiceOffered']                = CloudServiceOfferedSerializer::class;
        // software

        $this->registry['OpenStackComponent']                 = OpenStackComponentSerializer::class;
        $this->registry['OpenStackRelease']                   = OpenStackReleaseSerializer::class;

        // ccla

        $this->registry['Team']                               = TeamSerializer::class;
    }

    /**
     * @param object $object
     * @param string $type
     * @return IModelSerializer
     */
    public function getSerializer($object, $type = self::SerializerType_Public){
        if(is_null($object)) return null;
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