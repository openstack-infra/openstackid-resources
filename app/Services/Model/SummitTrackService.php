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
use App\Models\Foundation\Summit\Factories\PresentationCategoryFactory;
use App\Models\Foundation\Summit\Repositories\ISummitTrackRepository;
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
        return $this->tx_service->transaction(function() use($summit, $data){

           $former_track =  $summit->getPresentationCategoryByCode($data['code']);
           if(!is_null($former_track))
               throw new ValidationException(sprintf("track id %s already has code %s assigned on summit id %s", $former_track->getId(), $data['code'], $summit->getId()));

            $former_track =  $summit->getPresentationCategoryByTitle($data['title']);
            if(!is_null($former_track))
                throw new ValidationException(sprintf("track id %s already has title %s assigned on summit id %s", $former_track->getId(), $data['title'], $summit->getId()));

            $track = PresentationCategoryFactory::build($summit, $data);

            return $track;

        });
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
        // TODO: Implement updateTrack() method.
    }

    /**
     * @param Summit $summit
     * @param int $track_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteEventType(Summit $summit, $track_id)
    {
        // TODO: Implement deleteEventType() method.
    }
}