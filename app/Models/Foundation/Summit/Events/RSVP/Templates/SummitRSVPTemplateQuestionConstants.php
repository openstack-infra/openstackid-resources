<?php namespace App\Models\Foundation\Summit\Events\RSVP\Templates;
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
use App\Models\Foundation\Summit\Events\RSVP\RSVPCheckBoxListQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPDropDownQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPLiteralContentQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPMemberEmailQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPMemberFirstNameQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPMemberLastNameQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPRadioButtonListQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPTextAreaQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPTextBoxQuestionTemplate;
/**
 * Class SummitRSVPTemplateQuestionConstants
 * @package App\Models\Foundation\Summit\Events\RSVP\Templates
 */
final class SummitRSVPTemplateQuestionConstants
{
    public static $valid_class_names = [
        RSVPMemberEmailQuestionTemplate::ClassName,
        RSVPMemberFirstNameQuestionTemplate::ClassName,
        RSVPMemberLastNameQuestionTemplate::ClassName,
        RSVPTextBoxQuestionTemplate::ClassName,
        RSVPTextAreaQuestionTemplate::ClassName,
        RSVPCheckBoxListQuestionTemplate::ClassName,
        RSVPRadioButtonListQuestionTemplate::ClassName,
        RSVPDropDownQuestionTemplate::ClassName,
        RSVPLiteralContentQuestionTemplate::ClassName,
    ];
}