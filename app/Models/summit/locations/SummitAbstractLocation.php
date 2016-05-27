<?php namespace models\summit;

/**
 * Copyright 2015 OpenStack Foundation
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

use models\utils\SilverstripeBaseModel;
use utils\Filter;
use utils\ExistsFilterManyManyMapping;
use utils\ExistsFilterManyToOneMapping;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitAbstractLocation")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"SummitAbstractLocation" = "SummitAbstractLocation", "SummitGeoLocatedLocation" = "SummitGeoLocatedLocation", "SummitExternalLocation" = "SummitExternalLocation", "SummitVenue" = "SummitVenue", "SummitHotel" = "SummitHotel", "SummitAirport" = "SummitAirport", "SummitVenueRoom" = "SummitVenueRoom"})
 * Class SummitAbstractLocation
 * @package models\summit
 */
class SummitAbstractLocation extends SilverstripeBaseModel
{
    const TypeExternal = 'External';
    const TypeInternal = 'Internal';
    const TypeNone     = 'None';

    public function __construct()
    {
        $this->type = self::TypeNone;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="ID", type="integer", unique=true, nullable=false)
     */
    protected $id;

    use SummitOwned;

    protected static $array_mappings = array
    (
        'ID'           => 'id:json_int',
        'Name'         => 'name:json_string',
        'Description'  => 'description:json_string',
        'ClassName'    => 'class_name',
        'LocationType' => 'location_type',
    );

    /**
     * @ORM\Column(name="Name", type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="Description", type="string")
     */
    protected $description;


    /**
     * @ORM\Column(name="LocationType", type="string")
     */
    protected $type;

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter|null $filter
     * @param bool|false $published
     * @return array
     */
    public function events($page = 1, $per_page = 100, Filter $filter = null, $published = false)
    {
        $rel = $this
            ->hasMany('models\summit\SummitEvent', 'LocationID', 'ID')
            ->select
            (
                array
                (
                    'SummitEvent.*',
                    'Presentation.Priority',
                    'Presentation.Level',
                    'Presentation.Status',
                    'Presentation.OtherTopic',
                    'Presentation.Progress',
                    'Presentation.Slug',
                    'Presentation.CreatorID',
                    'Presentation.CategoryID',
                    'Presentation.Views',
                    'Presentation.ModeratorID',
                    'Presentation.ProblemAddressed',
                    'Presentation.AttendeesExpectedLearnt',
                    'Presentation.SelectionMotive',
                )
            );

        $rel = $rel->leftJoin('Presentation', 'SummitEvent.ID', '=', 'Presentation.ID');

        if($published)
        {
            $rel = $rel->where('Published','=','1');
        }

        if(!is_null($filter))
        {
            $filter->apply2Relation($rel, array
            (
                'title'         => 'SummitEvent.Title',
                'start_date'    => 'SummitEvent.StartDate:datetime_epoch',
                'end_date'      => 'SummitEvent.EndDate:datetime_epoch',
                'tags'          => new ExistsFilterManyManyMapping
                (
                    'Tag',
                    'SummitEvent_Tags',
                    'SummitEvent_Tags.TagID = Tag.ID',
                    "SummitEvent_Tags.SummitEventID = SummitEvent.ID AND Tag.Tag :operator ':value'"
                ),
                'summit_type_id' => new ExistsFilterManyManyMapping
                (
                    'SummitType',
                    'SummitEvent_AllowedSummitTypes',
                    'SummitType.ID = SummitEvent_AllowedSummitTypes.SummitTypeID',
                    'SummitEvent_AllowedSummitTypes.SummitEventID = SummitEvent.ID AND SummitType.ID :operator :value'
                ),
                'event_type_id'  => new ExistsFilterManyToOneMapping
                (
                    'SummitEventType',
                    'SummitEventType.ID = SummitEvent.TypeID AND SummitEventType.ID :operator :value'
                ),
                'track_id'     => new ExistsFilterManyToOneMapping
                (
                    'PresentationCategory',
                    'PresentationCategory.ID = Presentation.CategoryID AND PresentationCategory.ID :operator :value'
                ),
                'speaker' => new ExistsFilterManyManyMapping
                (
                    'PresentationSpeaker',
                    'Presentation_Speakers',
                    'Presentation_Speakers.PresentationSpeakerID = PresentationSpeaker.ID',
                    "Presentation_Speakers.PresentationID = SummitEvent.ID AND CONCAT(FirstName, ' ' , LastName) :operator ':value'"
                ),
            ));
        }

        $rel = $rel->orderBy('StartDate','asc')->orderBy('EndDate','asc');

        $pagination_result = $rel->paginate($per_page);
        $total             = $pagination_result->total();
        $items             = $pagination_result->items();
        $per_page          = $pagination_result->perPage();
        $current_page      = $pagination_result->currentPage();
        $last_page         = $pagination_result->lastPage();
        $events            = array();

        foreach($items as $e)
        {
            if($e->ClassName === 'Presentation')
                $e = Presentation::toPresentation($e);
            array_push($events, $e);
        }
        return array($total,$per_page, $current_page, $last_page, $events);
    }


}