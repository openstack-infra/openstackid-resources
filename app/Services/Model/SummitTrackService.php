<?php namespace App\Services\Model;

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
use App\Events\TrackDeleted;
use App\Events\TrackInserted;
use App\Events\TrackUpdated;
use App\Models\Foundation\Summit\Factories\PresentationCategoryFactory;
use App\Models\Foundation\Summit\Repositories\ISummitTrackRepository;
use Illuminate\Support\Facades\Event;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\PresentationCategory;
use models\summit\Summit;
/**
 * Class SummitTrackService
 * @package App\Services\Model
 */
final class SummitTrackService implements ISummitTrackService
{
    /**
     * @var ISummitTrackRepository
     */
    private $repository;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * SummitTrackService constructor.
     * @param ISummitTrackRepository $repository
     * @param ITransactionService $tx_service
     */
    public function __construct(ISummitTrackRepository $repository, ITransactionService $tx_service)
    {
        $this->repository = $repository;
        $this->tx_service = $tx_service;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return PresentationCategory
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrack(Summit $summit, array $data)
    {
        $track =  $this->tx_service->transaction(function () use ($summit, $data) {

            if (!empty($data['code'])) {
                $former_track = $summit->getPresentationCategoryByCode(trim($data['code']));
                if (!is_null($former_track))
                    throw new ValidationException(sprintf("track id %s already has code %s assigned on summit id %s", $former_track->getId(), $data['code'], $summit->getId()));
            }

            $former_track = $summit->getPresentationCategoryByTitle($data['title']);
            if (!is_null($former_track))
                throw new ValidationException(sprintf("track id %s already has title %s assigned on summit id %s", $former_track->getId(), $data['title'], $summit->getId()));

            $track = PresentationCategoryFactory::build($summit, $data);

            $summit->addPresentationCategory($track);

        });

        Event::fire(new TrackInserted($track->getSummitId(), $track->getId()));

        return $track;
    }

    /**
     * @param Summit $summit
     * @param int $track_id
     * @param array $data
     * @return PresentationCategory
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrack(Summit $summit, $track_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_id, $data) {

            $track = $summit->getPresentationCategory($track_id);

            if (is_null($track))
                throw new EntityNotFoundException
                (
                    sprintf("track id %s does not belong to summit id %s", $track_id, $summit->getId())
                );

            if (isset($data['code']) && !empty($data['code'])) {
                $former_track = $summit->getPresentationCategoryByCode($data['code']);
                if (!is_null($former_track) && $former_track->getId() != $track_id)
                    throw new ValidationException(sprintf("track id %s already has code %s assigned on summit id %s", $former_track->getId(), $data['code'], $summit->getId()));
            }

            if (isset($data['title'])) {
                $former_track = $summit->getPresentationCategoryByTitle($data['title']);
                if (!is_null($former_track) && $former_track->getId() != $track_id)
                    throw new ValidationException(sprintf("track id %s already has title %s assigned on summit id %s", $former_track->getId(), $data['title'], $summit->getId()));
            }


            $track = PresentationCategoryFactory::populate($track, $data);

            Event::fire(new TrackUpdated($track->getSummitId(), $track->getId()));

            return $track;

        });
    }

    /**
     * @param Summit $summit
     * @param int $track_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTrack(Summit $summit, $track_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_id) {

            $track = $summit->getPresentationCategory($track_id);

            if (is_null($track))
                throw new EntityNotFoundException
                (
                    sprintf("track id %s does not belong to summit id %s", $track_id, $summit->getId())
                );

            $summit_events = $track->getRelatedPublishedSummitEventsIds();

            if(count($summit_events) > 0){
                throw new ValidationException
                (
                    sprintf("track id %s could not be deleted bc its assigned to published events on summit id %s", $track_id, $summit->getId())
                );
            }

            Event::fire(new TrackDeleted($track->getSummitId(), $track->getId()));

            $this->repository->delete($track);
        });
    }
}