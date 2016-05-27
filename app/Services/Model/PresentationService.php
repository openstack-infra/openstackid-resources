<?php namespace services\model;
/**
 * Copyright 2016 OpenStack Foundation
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

use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\factories\IPresentationVideoFactory;
use models\summit\ISummitEventRepository;
use models\summit\PresentationVideo;
use libs\utils\ITransactionService;

/**
 * Class PresentationService
 * @package services\model
 */
final class PresentationService implements IPresentationService
{
    /**
     * @var ISummitEventRepository
     */
    private $presentation_repository;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var IPresentationVideoFactory
     */
    private $video_factory;

    public function __construct
    (
       IPresentationVideoFactory $video_factory,
       ISummitEventRepository $presentation_repository ,
       ITransactionService $tx_service
    )
    {
        $this->presentation_repository = $presentation_repository;
        $this->video_factory           = $video_factory;
        $this->tx_service              = $tx_service;
    }

    /**
     * @param int $presentation_id
     * @param array $video_data
     * @return PresentationVideo
     */
    public function addVideoTo($presentation_id, array $video_data)
    {
        return $this->tx_service->transaction(function() use($presentation_id, $video_data){

            $presentation = $this->presentation_repository->getById($presentation_id);

            if(is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if($presentation->hasVideos())
                throw new ValidationException(sprintf('presentation %s already has a video!', $presentation_id));

            if(!isset($video_data['name'])) $video_data['name'] = $presentation->Title;

            $video = $this->video_factory->build($video_data);

            $presentation->addVideo($video);
        });
    }
}