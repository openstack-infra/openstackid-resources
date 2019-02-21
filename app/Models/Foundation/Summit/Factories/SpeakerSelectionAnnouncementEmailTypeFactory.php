<?php namespace models\summit\factories;
use models\summit\PresentationSpeaker;
use models\summit\SpeakerAnnouncementSummitEmail;
use models\summit\Summit;

/**
 * Copyright 2018 OpenStack Foundation
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

final class SpeakerSelectionAnnouncementEmailTypeFactory
{

    public static function build(Summit $summit, PresentationSpeaker $speaker, $role = PresentationSpeaker::RoleSpeaker)
    {
        $has_published = $speaker->hasPublishedRegularPresentations($summit, $role, true, $summit->getExcludedCategoriesForAcceptedPresentations()) ||
                         $speaker->hasPublishedLightningPresentations($summit, $role, true, $summit->getExcludedCategoriesForAcceptedPresentations());
        $has_rejected  = $speaker->hasRejectedPresentations($summit, $role, true, $summit->getExcludedCategoriesForRejectedPresentations());
        $has_alternate = $speaker->hasAlternatePresentations($summit, $role, true, $summit->getExcludedCategoriesForAcceptedPresentations());

        if($has_published && !$has_rejected && !$has_alternate)
            return SpeakerAnnouncementSummitEmail::TypeAccepted;

        if(!$has_published && !$has_rejected && $has_alternate)
            return SpeakerAnnouncementSummitEmail::TypeAlternate;

        if(!$has_published && $has_rejected && !$has_alternate)
            return SpeakerAnnouncementSummitEmail::TypeRejected;

        if($has_published && !$has_rejected && $has_alternate)
            return SpeakerAnnouncementSummitEmail::TypeAcceptedAlternate;

        if($has_published && $has_rejected && !$has_alternate)
            return SpeakerAnnouncementSummitEmail::TypeAcceptedRejected;

        if(!$has_published && $has_rejected && $has_alternate)
            return SpeakerAnnouncementSummitEmail::TypeAcceptedRejected;

        if($has_published && $has_rejected && $has_alternate)
            return SpeakerAnnouncementSummitEmail::TypeAcceptedAlternate;

        return SpeakerAnnouncementSummitEmail::TypeNone;
    }
}