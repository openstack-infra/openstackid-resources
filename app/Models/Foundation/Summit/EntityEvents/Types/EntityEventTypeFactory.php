<?php namespace Models\foundation\summit\EntityEvents;

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

use models\summit\SummitEntityEvent;
use InvalidArgumentException;

/**
 * Class EntityEventTypeFactory
 * @package Models\foundation\summit\EntityEvents
 */
final class EntityEventTypeFactory
{
    /**
     * @var EntityEventTypeFactory
     */
    private static $instance;

    private function __clone(){}

    /**
     * @return EntityEventTypeFactory
     */
    public static function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new EntityEventTypeFactory();
        }

        return self::$instance;
    }

    /**
     * @param SummitEntityEvent $e
     * @param SummitEntityEventProcessContext $ctx
     * @return IEntityEventType
     */
    public function build(SummitEntityEvent $e, SummitEntityEventProcessContext $ctx){

        switch($e->getEntityClassName())
        {
            case 'Presentation':
            case 'SummitEvent':
            case 'SummitEventWithFile':
            {
                if ($e->getType() === 'UPDATE' || $e->getType() === "INSERT")
                    return new SummitEventEntityEventInsertOrUpdateType($e, $ctx);
                return new SummitEventEntityEventDeleteType($e, $ctx);
            }
            break;
            case 'SummitGroupEvent':
            {
                if ($e->getType() === 'UPDATE' || $e->getType() === "INSERT")
                    return new SummitGroupEventEntityEventInsertOrUpdateType($e, $ctx);

                return new SummitEventEntityEventDeleteType($e, $ctx);
            }
            break;
            case 'MySchedule':
            case 'MyFavorite':
            {
                return new MyScheduleEntityEventType($e, $ctx);
            }
            break;
            case 'Summit':
            {
                return new SummitEntityEventType($e, $ctx);
            }
            break;
            case 'SummitEventType':
            case 'PresentationType':
            {
                return new SummitEventTypeEntityEventType($e, $ctx);
            }
            break;
            case 'SummitWIFIConnection': {
                return new SummitWIFIConnectionEntityEventType($e, $ctx);
            }
            break;
            case 'SummitVenue':
            case 'SummitVenueRoom':
            case 'SummitAirport':
            case 'SummitHotel':
            case 'SummitGeoLocatedLocation':
            case 'SummitExternalLocation':
            case 'SummitAbstractLocation':
            case 'VenueFloorFromVenueRoom':
            {
                return new SummitLocationEntityEventType($e, $ctx);
            }
            break;
            case 'Speaker':
            {
                return new PresentationSpeakerEntityEventType($e, $ctx);
            }
            break;
            case 'SummitTicketType':
            {
                return new SummitTicketTypeEntityEventType($e, $ctx);
            }
            break;
            case 'SummitLocationImage':
            case 'SummitLocationMap':
            {
                return new SummitLocationImageEventType($e, $ctx);
            }
            break;
            case 'PresentationCategory':
            {
                return new PresentationCategoryEntityEventType($e, $ctx);
            }
            break;
            case 'PresentationCategoryGroup':
            case 'PrivatePresentationCategoryGroup':
            {
                return new PresentationCategoryGroupEntityEventType($e, $ctx);
            }
            break;
            case 'PresentationSlide':
            case 'PresentationVideo':
            case 'PresentationLink':
            {
                return new PresentationMaterialEntityEventType($e, $ctx);
            }
            break;
            case 'SpeakerFromPresentation':
            case 'SummitTypeFromEvent':
            case 'SponsorFromEvent':
            {
                return new SummitEventRelationEntityEventType($e, $ctx);
            }
            break;
            case 'TrackFromTrackGroup':
            {
                return new TrackFromTrackGroupEventType($e, $ctx);
            }
            break;
            case 'SummitVenueFloor':
            {
                return new SummitVenueFloorEntityEventType($e, $ctx);
            }
            break;
            case 'WipeData':
            {
                return new  WipeDataEntityEventType($e, $ctx);
            }
            break;
            default:
                throw new InvalidArgumentException(sprintf('invalid entity class name %s!', $e->getEntityClassName()));
            break;
        }
    }
}