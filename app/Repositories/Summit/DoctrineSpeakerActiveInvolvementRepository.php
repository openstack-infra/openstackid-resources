<?php namespace App\Repositories\Summit;
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
use App\Models\Foundation\Summit\Repositories\ISpeakerActiveInvolvementRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use models\summit\SpeakerActiveInvolvement;
/**
 * Class DoctrineSpeakerActiveInvolvementRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSpeakerActiveInvolvementRepository
    extends SilverStripeDoctrineRepository
    implements ISpeakerActiveInvolvementRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return SpeakerActiveInvolvement::class;
    }

    /**
     * @return SpeakerActiveInvolvement[]
     */
    public function getDefaultOnes()
    {
        return $this->findBy(["is_default" => true]);
    }
}