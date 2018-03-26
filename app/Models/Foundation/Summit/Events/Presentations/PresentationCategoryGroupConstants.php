<?php namespace App\Models\Foundation\Summit\Events\Presentations;
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
use models\summit\PresentationCategoryGroup;
use models\summit\PrivatePresentationCategoryGroup;
/**
 * Class PresentationCategoryGroupConstants
 * @package App\Models\Foundation\Summit\Events\Presentations
 */
final class PresentationCategoryGroupConstants
{
    public static $valid_class_names = [
        PresentationCategoryGroup::ClassName,
        PrivatePresentationCategoryGroup::ClassName,
    ];
}