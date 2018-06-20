<?php
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
use Illuminate\Database\Seeder;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Illuminate\Support\Facades\DB;
use App\Models\Foundation\Summit\Defaults\DefaultSummitEventType;
use App\Models\Foundation\Summit\Defaults\DefaultPresentationType;
use LaravelDoctrine\ORM\Facades\Registry;
use models\summit\IPresentationType;
use models\summit\ISummitEventType;
/**
 * Class DefaultEventTypesSeeder
 */
final class DefaultEventTypesSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $em                = Registry::getManager('ss');
        $repo              = $em->getRepository(DefaultSummitEventType::class);

        // presentation default types

        $presentation_type = $repo->getByType(IPresentationType::Presentation);

        if(is_null($presentation_type)){
            $presentation_type = new DefaultPresentationType();
            $presentation_type->setType(IPresentationType::Presentation);
            $presentation_type->setMinSpeakers(1);
            $presentation_type->setMaxSpeakers(3);
            $presentation_type->setMinModerators(0);
            $presentation_type->setMaxModerators(0);
            $presentation_type->setUseSpeakers(true);
            $presentation_type->setShouldBeAvailableOnCfp(true);
            $presentation_type->setAreSpeakersMandatory(false);
            $presentation_type->setUseModerator(false);
            $presentation_type->setIsModeratorMandatory(false);
            $em->persist($presentation_type);
        }

        $key_note = $repo->getByType(IPresentationType::Keynotes);

        if(is_null($key_note)){
            $key_note = new DefaultPresentationType();
            $key_note->setType(IPresentationType::Keynotes);
            $key_note->setMinSpeakers(1);
            $key_note->setMaxSpeakers(3);
            $key_note->setMinModerators(0);
            $key_note->setMaxModerators(1);
            $key_note->setUseSpeakers(true);
            $key_note->setShouldBeAvailableOnCfp(true);
            $key_note->setAreSpeakersMandatory(false);
            $key_note->setUseModerator(true);
            $key_note->setIsModeratorMandatory(false);
            $em->persist($key_note);
        }

        $panel = $repo->getByType(IPresentationType::Panel);

        if(is_null($panel)){
            $panel = new DefaultPresentationType();
            $panel->setType(IPresentationType::Panel);
            $panel->setMinSpeakers(1);
            $panel->setMaxSpeakers(3);
            $panel->setMinModerators(0);
            $panel->setMaxModerators(1);
            $panel->setUseSpeakers(true);
            $panel->setShouldBeAvailableOnCfp(true);
            $panel->setAreSpeakersMandatory(false);
            $panel->setUseModerator(true);
            $panel->setIsModeratorMandatory(false);
            $em->persist($panel);
        }

        $lighting_talks = $repo->getByType(IPresentationType::LightingTalks);

        if(is_null($lighting_talks)){
            $lighting_talks = new DefaultPresentationType();
            $lighting_talks->setType(IPresentationType::LightingTalks);
            $lighting_talks->setMinSpeakers(1);
            $lighting_talks->setMaxSpeakers(3);
            $lighting_talks->setMinModerators(0);
            $lighting_talks->setMaxModerators(0);
            $lighting_talks->setUseSpeakers(true);
            $lighting_talks->setShouldBeAvailableOnCfp(true);
            $lighting_talks->setAreSpeakersMandatory(false);
            $lighting_talks->setUseModerator(false);
            $lighting_talks->setIsModeratorMandatory(false);
            $em->persist($lighting_talks);
        }

        $fishbowl = $repo->getByType(IPresentationType::Fishbowl);

        if(is_null($fishbowl)){
            $fishbowl = new DefaultPresentationType();
            $fishbowl->setType(IPresentationType::Fishbowl);
            $fishbowl->setMinSpeakers(0);
            $fishbowl->setMaxSpeakers(2);
            $fishbowl->setMinModerators(0);
            $fishbowl->setMaxModerators(2);
            $fishbowl->setUseSpeakers(true);
            $fishbowl->setShouldBeAvailableOnCfp(false);
            $fishbowl->setAreSpeakersMandatory(false);
            $fishbowl->setUseModerator(true);
            $fishbowl->setIsModeratorMandatory(false);
            $em->persist($fishbowl);
        }

        $workshop = $repo->getByType(IPresentationType::Workshop);

        if(is_null($fishbowl)){
            $workshop = new DefaultPresentationType();
            $workshop->setType(IPresentationType::Workshop);
            $workshop->setMinSpeakers(1);
            $workshop->setMaxSpeakers(4);
            $workshop->setMinModerators(0);
            $workshop->setMaxModerators(0);
            $workshop->setUseSpeakers(true);
            $workshop->setShouldBeAvailableOnCfp(true);
            $workshop->setAreSpeakersMandatory(false);
            $workshop->setUseModerator(false);
            $workshop->setIsModeratorMandatory(false);
            $em->persist($workshop);
        }

        // events default types

        $lunch = $repo->getByType(ISummitEventType::Lunch);
        if (is_null($lunch)) {
            $lunch = new DefaultSummitEventType();
            $lunch->setType(ISummitEventType::Lunch);
            $lunch->setAllowsAttachment(true);
            $em->persist($lunch);
        }

        $hand_on_labs = $repo->getByType(ISummitEventType::HandonLabs);
        if (is_null($hand_on_labs)) {
            $hand_on_labs = new DefaultSummitEventType();
            $hand_on_labs->setType(ISummitEventType::HandonLabs);
            $em->persist($hand_on_labs);
        }

        $evening_events = $repo->getByType(ISummitEventType::EveningEvents);
        if (is_null($evening_events)) {
            $evening_events = new DefaultSummitEventType();
            $evening_events->setType(ISummitEventType::EveningEvents);
            $em->persist($evening_events);
        }

        $breaks = $repo->getByType(ISummitEventType::Breaks);
        if (is_null($breaks)) {
            $breaks = new DefaultSummitEventType();
            $breaks->setType(ISummitEventType::Breaks);
            $em->persist($breaks);
        }

        $breakfast = $repo->getByType(ISummitEventType::Breakfast);
        if (is_null($breakfast)) {
            $breakfast = new DefaultSummitEventType();
            $breakfast->setType(ISummitEventType::Breakfast);
            $em->persist($breakfast);
        }

        $marketplace_hours = $repo->getByType(ISummitEventType::MarketplaceHours);
        if (is_null($marketplace_hours)) {
            $marketplace_hours = new DefaultSummitEventType();
            $marketplace_hours->setType(ISummitEventType::MarketplaceHours);
            $em->persist($marketplace_hours);
        }

        $em->flush();

    }
}