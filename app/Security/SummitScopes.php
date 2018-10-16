<?php namespace App\Security;
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

/**
 * Class SummitScopes
 * @package App\Security
 */
final class SummitScopes
{
    const ReadSummitData     = '%s/summits/read';
    const ReadAllSummitData  = '%s/summits/read/all';
    const ReadNotifications  = '%s/summits/read-notifications';
    const WriteNotifications  = '%s/summits/write-notifications';

    const WriteSummitData    = '%s/summits/write';
    const WriteSpeakersData  = '%s/speakers/write';
    const WriteTrackTagGroupsData  = '%s/track-tag-groups/write';
    const WriteTrackQuestionTemplateData  = '%s/track-question-templates/write';
    const WriteMySpeakersData  = '%s/speakers/write/me';

    const PublishEventData   = '%s/summits/publish-event';
    const WriteEventData     = '%s/summits/write-event';
    const WriteVideoData     = '%s/summits/write-videos';

    const WriteAttendeesData = '%s/attendees/write';

    const WritePromoCodeData = '%s/promo-codes/write';

    const WriteEventTypeData = '%s/event-types/write';

    const WriteTracksData    = '%s/tracks/write';

    const WriteTrackGroupsData    = '%s/track-groups/write';

    const WriteLocationsData    = '%s/locations/write';

    const WriteRSVPTemplateData    = '%s/rsvp-templates/write';

    const WriteLocationBannersData = '%s/locations/banners/write';

    const WriteSummitSpeakerAssistanceData = '%s/summit-speaker-assistance/write';

    const WriteTicketTypeData = '%s/ticket-types/write';

    const WritePresentationData     = '%s/summits/write-presentation';
}