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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackCheckBoxListQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackCheckBoxQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackDropDownQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackLiteralContentQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackRadioButtonListQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackTextBoxQuestionTemplate;
use App\Models\Foundation\Summit\Repositories\ITrackQuestionTemplateRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use utils\DoctrineInstanceOfFilterMapping;

/**
 * Class DoctrineTrackQuestionTemplateRepository
 * @package App\Repositories\Summit
 */
final class DoctrineTrackQuestionTemplateRepository
    extends SilverStripeDoctrineRepository
    implements ITrackQuestionTemplateRepository
{

    protected function getFilterMappings()
    {
        return [
            'name'  => 'e.name:json_string',
            'label' => 'e.label:json_string',
            'class_name'            => new DoctrineInstanceOfFilterMapping
            (
                "e",
                [
                    TrackLiteralContentQuestionTemplate::ClassName  => TrackLiteralContentQuestionTemplate::class,
                    TrackRadioButtonListQuestionTemplate::ClassName => TrackRadioButtonListQuestionTemplate::class,
                    TrackCheckBoxListQuestionTemplate::ClassName => TrackCheckBoxListQuestionTemplate::class,
                    TrackDropDownQuestionTemplate::ClassName => TrackDropDownQuestionTemplate::class,
                    TrackTextBoxQuestionTemplate::ClassName => TrackTextBoxQuestionTemplate::class,
                    TrackCheckBoxQuestionTemplate::ClassName => TrackCheckBoxQuestionTemplate::class,
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'name'  => 'e.name',
            'label' => 'e.name',
            'id'    => 'e.id',
        ];
    }

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return TrackQuestionTemplate::class;
    }

    /**
     * @param string $name
     * @return TrackQuestionTemplate
     */
    public function getByName($name)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("g")
            ->from(TrackQuestionTemplate::class, "g")
            ->where('LOWER(g.name) = :name')
            ->setParameter('name', strtolower(trim($name)))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $label
     * @return TrackQuestionTemplate
     */
    public function getByLabel($label)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("g")
            ->from(TrackQuestionTemplate::class, "g")
            ->where('LOWER(g.label) = :label')
            ->setParameter('label', strtolower(trim($label)))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getQuestionsMetadata()
    {
        return [
            TrackTextBoxQuestionTemplate::getMetadata(),
            TrackCheckBoxQuestionTemplate::getMetadata(),
            TrackLiteralContentQuestionTemplate::getMetadata(),
            TrackRadioButtonListQuestionTemplate::getMetadata(),
            TrackCheckBoxListQuestionTemplate::getMetadata(),
            TrackDropDownQuestionTemplate::getMetadata(),
        ];
    }
}