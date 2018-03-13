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
use App\Events\FloorDeleted;
use App\Events\FloorInserted;
use App\Events\FloorUpdated;
use App\Events\LocationDeleted;
use App\Events\LocationImageDeleted;
use App\Events\LocationImageInserted;
use App\Events\LocationImageUpdated;
use App\Events\LocationInserted;
use App\Events\LocationUpdated;
use App\Events\SummitVenueRoomDeleted;
use App\Events\SummitVenueRoomInserted;
use App\Events\SummitVenueRoomUpdated;
use App\Http\Utils\FileUploader;
use App\Models\Foundation\Summit\Factories\SummitLocationBannerFactory;
use App\Models\Foundation\Summit\Factories\SummitLocationFactory;
use App\Models\Foundation\Summit\Factories\SummitLocationImageFactory;
use App\Models\Foundation\Summit\Factories\SummitVenueFloorFactory;
use App\Models\Foundation\Summit\Locations\Banners\ScheduledSummitLocationBanner;
use App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner;
use App\Models\Foundation\Summit\Repositories\ISummitLocationRepository;
use App\Services\Apis\GeoCodingApiException;
use App\Services\Apis\IGeoCodingAPI;
use App\Services\Model\Strategies\GeoLocation\GeoLocationStrategyFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IFolderRepository;
use models\summit\Summit;
use models\summit\SummitAbstractLocation;
use models\summit\SummitAirport;
use models\summit\SummitExternalLocation;
use models\summit\SummitGeoLocatedLocation;
use models\summit\SummitHotel;
use models\summit\SummitLocationImage;
use models\summit\SummitVenue;
use models\summit\SummitVenueFloor;
use models\summit\SummitVenueRoom;
/**
 * Class LocationService
 * @package App\Services\Model
 */
final class LocationService implements ILocationService
{
    /**
     * @var ISummitLocationRepository
     */
    private $location_repository;

    /**
     * @var IFolderRepository
     */
    private $folder_repository;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var IGeoCodingAPI
     */
    private $geo_coding_api;

    /**
     * LocationService constructor.
     * @param ISummitLocationRepository $location_repository
     * @param IFolderRepository $folder_repository
     * @param IGeoCodingAPI $geo_coding_api
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitLocationRepository $location_repository,
        IFolderRepository $folder_repository,
        IGeoCodingAPI $geo_coding_api,
        ITransactionService $tx_service
    )
    {
        $this->location_repository = $location_repository;
        $this->geo_coding_api      = $geo_coding_api;
        $this->tx_service          = $tx_service;
        $this->folder_repository   = $folder_repository;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return SummitAbstractLocation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addLocation(Summit $summit, array $data)
    {
        $location =  $this->tx_service->transaction(function () use ($summit, $data) {

            $old_location = $summit->getLocationByName(trim($data['name']));

            if (!is_null($old_location)) {
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.LocationService.addLocation.LocationNameAlreadyExists',
                        [
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            $location = SummitLocationFactory::build($data);

            if (is_null($location)) {
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.LocationService.addLocation.InvalidClassName'
                    )
                );
            }

            if ($location instanceof SummitGeoLocatedLocation) {
                try {
                    $geo_location_strategy = GeoLocationStrategyFactory::build($location);
                    $geo_location_strategy->doGeoLocation($location, $this->geo_coding_api);
                } catch (GeoCodingApiException $ex1) {
                    Log::warning($ex1->getMessage());
                    $validation_msg = trans('validation_errors.LocationService.addLocation.geoCodingGenericError');
                    switch ($ex1->getStatus()) {
                        case IGeoCodingAPI::ResponseStatusZeroResults: {
                            $validation_msg = trans('validation_errors.LocationService.addLocation.InvalidAddressOrCoordinates');
                        }
                        break;
                        case IGeoCodingAPI::ResponseStatusOverQueryLimit: {
                            $validation_msg = trans('validation_errors.LocationService.addLocation.OverQuotaLimit');
                        }
                        break;
                    }
                    throw new ValidationException($validation_msg);
                } catch (\Exception $ex) {
                    Log::warning($ex->getMessage());
                    throw $ex;
                }
            }

            $summit->addLocation($location);

            return $location;
        });

        Event::fire
        (
            new LocationInserted
            (
                $location->getSummitId(),
                $location->getId(),
                $location->getClassName()
            )
        );

        return $location;
    }

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param array $data
     * @return SummitAbstractLocation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateLocation(Summit $summit, $location_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $location_id, $data) {

            if (isset($data['name'])) {
                $old_location = $summit->getLocationByName(trim($data['name']));

                if (!is_null($old_location) && $old_location->getId() != $location_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.LocationService.updateLocation.LocationNameAlreadyExists',
                            [
                                'summit_id' => $summit->getId()
                            ]
                        )
                    );
                }
            }

            $location = $summit->getLocation($location_id);

            if (is_null($location)) {
                throw new EntityNotFoundException(
                    trans
                    (
                        'validation_errors.LocationService.updateLocation.LocationNotFoundOnSummit',
                        [
                            'summit_id' => $summit->getId(),
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            if(!Summit::isPrimaryLocation($location)){
                 throw new EntityNotFoundException(
                    trans
                    (
                        'validation_errors.LocationService.updateLocation.LocationNotFoundOnSummit',
                        [
                            'summit_id' => $summit->getId(),
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            if ($location->getClassName() != $data['class_name']) {
                throw new ValidationException(
                    trans
                    (
                        'validation_errors.LocationService.updateLocation.ClassNameMissMatch',
                        [
                            'summit_id'   => $summit->getId(),
                            'location_id' => $location_id,
                            'class_name'  => $data['class_name']
                        ]
                    )
                );
            }

            $location = SummitLocationFactory::populate($location, $data);

            if ($location instanceof SummitGeoLocatedLocation && $this->hasGeoLocationData2Update($data)) {
                try {
                    $geo_location_strategy = GeoLocationStrategyFactory::build($location);
                    $geo_location_strategy->doGeoLocation($location, $this->geo_coding_api);
                } catch (GeoCodingApiException $ex1) {
                    Log::warning($ex1->getMessage());
                    $validation_msg = trans('validation_errors.LocationService.addLocation.geoCodingGenericError');
                    switch ($ex1->getStatus()) {
                        case IGeoCodingAPI::ResponseStatusZeroResults: {
                            $validation_msg = trans('validation_errors.LocationService.addLocation.InvalidAddressOrCoordinates');
                        }
                            break;
                        case IGeoCodingAPI::ResponseStatusOverQueryLimit: {
                            $validation_msg = trans('validation_errors.LocationService.addLocation.OverQuotaLimit');
                        }
                            break;
                    }
                    throw new ValidationException($validation_msg);
                } catch (\Exception $ex) {
                    Log::warning($ex->getMessage());
                    throw $ex;
                }
            }

            if (isset($data['order']) && intval($data['order']) != $location->getOrder()) {
                // request to update order
                $summit->recalculateLocationOrder($location, intval($data['order']));
            }

            Event::fire
            (
                new LocationUpdated
                (
                    $location->getSummitId(),
                    $location->getId(),
                    $location->getClassName(),
                    $summit->getScheduleEventsIdsPerLocation($location)
                )
            );

            return $location;
        });
    }

    /**
     * @param array $data
     * @return bool
     */
    private function hasGeoLocationData2Update(array $data){
        return isset($data['address_1']) || isset($data['lat']);
    }

    /**
     * @param Summit $summit
     * @param int $location_id
     * @return SummitAbstractLocation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteLocation(Summit $summit, $location_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $location_id) {

            $location = $summit->getLocation($location_id);

            if (is_null($location)) {
                throw new EntityNotFoundException(
                    trans
                    (
                        'validation_errors.LocationService.deleteLocation.LocationNotFoundOnSummit',
                        [
                            'summit_id' => $summit->getId(),
                            'location_id' => $location_id,
                        ]
                    )
                );
            }
            if(!Summit::isPrimaryLocation($location)){
                throw new EntityNotFoundException(
                    trans
                    (
                        'validation_errors.LocationService.deleteLocation.LocationNotFoundOnSummit',
                        [
                            'summit_id' => $summit->getId(),
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            Event::fire(new LocationDeleted
                (
                    $location->getSummitId(),
                    $location->getId(),
                    $location->getClassName(),
                    $summit->getScheduleEventsIdsPerLocation($location))
            );

            $summit->removeLocation($location);
        });
    }

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param array $data
     * @return SummitVenueFloor
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addVenueFloor(Summit $summit, $venue_id, array $data)
    {
        $floor = $this->tx_service->transaction(function () use ($summit, $venue_id, $data) {

            $venue = $summit->getLocation($venue_id);

            if(is_null($venue)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.addVenueFloor.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            if(!$venue instanceof SummitVenue){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.addVenueFloor.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            $former_floor = $venue->getFloorByName(trim($data['name']));

            if(!is_null($former_floor)){
                throw new ValidationException(
                    trans
                    (
                        'validation_errors.LocationService.addVenueFloor.FloorNameAlreadyExists',
                        [
                            'venue_id'    => $venue_id,
                            'floor_name'  => trim($data['name'])
                        ]
                    )
                );
            }

            $former_floor = $venue->getFloorByNumber(intval($data['number']));

            if(!is_null($former_floor)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.LocationService.addVenueFloor.FloorNumberAlreadyExists',
                        [
                            'venue_id'     => $venue_id,
                            'floor_number' => intval($data['number'])
                        ]
                    )
                );
            }

            $floor = SummitVenueFloorFactory::build($data);

            $venue->addFloor($floor);

            return $floor;
        });

        Event::fire
        (
            new FloorInserted
            (
                $floor->getVenue()->getSummitId(),
                $floor->getVenueId(),
                $floor->getId()
            )
        );

        return $floor;
    }

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $floor_id
     * @param array $data
     * @return SummitVenueFloor
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateVenueFloor(Summit $summit, $venue_id, $floor_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $venue_id, $floor_id, $data) {

            $venue = $summit->getLocation($venue_id);

            if(is_null($venue)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateVenueFloor.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            if(!$venue instanceof SummitVenue){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateVenueFloor.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            if(isset($data['name'])) {
                $former_floor = $venue->getFloorByName(trim($data['name']));

                if (!is_null($former_floor) && $former_floor->getId() != $floor_id) {
                    throw new ValidationException(
                        trans
                        (
                            'validation_errors.LocationService.addVenueFloor.FloorNameAlreadyExists',
                            [
                                'venue_id' => $venue_id,
                                'floor_name' => trim($data['name'])
                            ]
                        )
                    );
                }
            }

            if(isset($data['number'])) {
                $former_floor = $venue->getFloorByNumber(intval($data['number']));

                if (!is_null($former_floor) && $former_floor->getId() != $floor_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.LocationService.addVenueFloor.FloorNumberAlreadyExists',
                            [
                                'venue_id' => $venue_id,
                                'floor_number' => intval($data['number'])
                            ]
                        )
                    );
                }

            }

            $floor = $venue->getFloor($floor_id);
            if(is_null($floor)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateVenueFloor.FloorNotFound',
                        [
                            'floor_id' => $floor_id,
                            'venue_id' => $venue_id
                        ]
                    )
                );
            }

            $floor = SummitVenueFloorFactory::populate($floor, $data);

            Event::fire
            (
                new FloorUpdated
                (
                    $floor->getVenue()->getSummitId(),
                    $floor->getVenueId(),
                    $floor->getId()
                )
            );

            return $floor;
        });
    }

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $floor_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteVenueFloor(Summit $summit, $venue_id, $floor_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $venue_id, $floor_id) {

            $venue = $summit->getLocation($venue_id);

            if(is_null($venue)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteVenueFloor.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            if(!$venue instanceof SummitVenue){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteVenueFloor.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            $floor = $venue->getFloor($floor_id);

            if(is_null($floor)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteVenueFloor.FloorNotFound',
                        [
                            'floor_id' => $floor_id,
                            'venue_id' => $venue_id
                        ]
                    )
                );
            }

            Event::fire(new FloorDeleted
                (
                    $floor->getVenue()->getSummitId(),
                    $floor->getVenueId(),
                    $floor->getId()
                )
            );

            $venue->removeFloor($floor);
        });
    }

    /**
     * @param Summit $summit
     * @param $venue_id
     * @param array $data
     * @return SummitVenueRoom
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addVenueRoom(Summit $summit, $venue_id, array $data)
    {
        $room =  $this->tx_service->transaction(function () use ($summit, $venue_id, $data) {

            if (isset($data['name'])) {
                $old_location = $summit->getLocationByName(trim($data['name']));

                if (!is_null($old_location)) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.LocationService.addVenueRoom.LocationNameAlreadyExists',
                            [
                                'summit_id' => $summit->getId()
                            ]
                        )
                    );
                }
            }

            $venue = $summit->getLocation($venue_id);

            if(is_null($venue)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.addVenueRoom.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            if(!$venue instanceof SummitVenue){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.addVenueRoom.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            $data['class_name'] = SummitVenueRoom::ClassName;
            $room               = SummitLocationFactory::build($data);

            if (is_null($room)) {
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.LocationService.addVenueRoom.InvalidClassName'
                    )
                );
            }

            if(isset($data['floor_id'])){
                $floor_id = intval($data['floor_id']);
                $floor = $venue->getFloor($floor_id);

                if(is_null($floor)){
                    throw new EntityNotFoundException
                    (
                        trans
                        (
                            'not_found_errors.LocationService.addVenueRoom.FloorNotFound',
                            [
                                'floor_id' => $floor_id,
                                'venue_id' => $venue_id
                            ]
                        )
                    );
                }

                $floor->addRoom($room);
            }

            $summit->addLocation($room);
            $venue->addRoom($room);

            return $room;
        });

        Event::fire
        (
            new SummitVenueRoomInserted
            (
                $room->getSummitId(),
                $room->getId(),
                $room->getClassName()
            )
        );

        return $room;
    }

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $room_id
     * @param array $data
     * @return SummitVenueRoom
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateVenueRoom(Summit $summit, $venue_id, $room_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $venue_id, $room_id, $data) {

            if (isset($data['name'])) {
                $old_location = $summit->getLocationByName(trim($data['name']));

                if (!is_null($old_location) && $old_location->getId() != $room_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.LocationService.updateVenueRoom.LocationNameAlreadyExists',
                            [
                                'summit_id' => $summit->getId()
                            ]
                        )
                    );
                }
            }

            $venue = $summit->getLocation($venue_id);

            if(is_null($venue)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateVenueRoom.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            if(!$venue instanceof SummitVenue){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateVenueRoom.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            $room = $summit->getLocation($room_id);
            if (is_null($room)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateVenueRoom.RoomNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                            'room_id'   => $room_id,
                        ]
                    )
                );
            }

            if (!$room instanceof SummitVenueRoom) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateVenueRoom.RoomNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                            'room_id'   => $room_id,
                        ]
                    )
                );
            }
            $old_floor_id = $room->getFloorId();
            $new_floor_id = $room->getFloorId();
            $room         = SummitLocationFactory::populate($room, $data);
            $floor        = null;
            if(isset($data['floor_id'])){
                $new_floor_id = intval($data['floor_id']);
                $floor        = $venue->getFloor($new_floor_id);

                if(is_null($floor)){
                    throw new EntityNotFoundException
                    (
                        trans
                        (
                            'not_found_errors.LocationService.updateVenueRoom.FloorNotFound',
                            [
                                'floor_id' => $new_floor_id,
                                'venue_id' => $venue_id
                            ]
                        )
                    );
                }

                $floor->addRoom($room);
            }

            $summit->addLocation($room);
            $venue->addRoom($room);

            // request to update order
            if (isset($data['order']) && intval($data['order']) != $room->getOrder()) {

                if(!is_null($floor)){
                    $floor->recalculateRoomsOrder($room, intval($data['order']));
                }
                else
                {
                    $venue->recalculateRoomsOrder($room, intval($data['order']));
                }
            }

            Event::fire
            (
                new SummitVenueRoomUpdated
                (
                    $room->getSummitId(),
                    $room->getId(),
                    $summit->getScheduleEventsIdsPerLocation($room),
                    $old_floor_id,
                    $new_floor_id
                )
            );

            return $room;
        });

    }

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $room_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteVenueRoom(Summit $summit, $venue_id, $room_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $venue_id, $room_id) {

            $venue = $summit->getLocation($venue_id);

            if(is_null($venue)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteVenueRoom.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            if(!$venue instanceof SummitVenue){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteVenueRoom.VenueNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'venue_id'  => $venue_id,
                        ]
                    )
                );
            }

            $room = $venue->getRoom($room_id);

            if(is_null($room)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteVenueRoom.RoomNotFound',
                        [
                            'room_id' => $room_id,
                            'venue_id' => $venue_id
                        ]
                    )
                );
            }

            $venue->removeRoom($room);

            if($room->hasFloor())
            {
                $floor = $room->getFloor();
                $floor->removeRoom($room);
            }

            Event::fire
            (
                new SummitVenueRoomDeleted
                (
                    $room->getSummitId(),
                    $room->getId(),
                    'SummitVenueRoom',
                    $summit->getScheduleEventsIdsPerLocation($room)
                )
            );
        });
    }

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param array $data
     * @return SummitLocationBanner
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addLocationBanner(Summit $summit, $location_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $location_id, $data) {

            $location = $summit->getLocation($location_id);

            if(is_null($location)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.addLocationBanner.LocationNotFound',
                        [
                            'summit_id' => $summit->getId(),
                            'location_id'  => $location_id,
                        ]
                    )
                );
            }

            $banner = SummitLocationBannerFactory::build($summit, $location, $data);

            if (is_null($banner)) {
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.LocationService.addLocationBanner.InvalidClassName'
                    )
                );
            }

            $location->addBanner($banner);

            return $banner;
        });
    }

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $banner_id
     * @param array $data
     * @return SummitLocationBanner
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateLocationBanner(Summit $summit, $location_id, $banner_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $location_id, $banner_id, $data) {

            $location = $summit->getLocation($location_id);

            if(is_null($location)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateLocationBanner.LocationNotFound',
                        [
                            'summit_id'   => $summit->getId(),
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            $banner = $location->getBannerById($banner_id);

            if(is_null($banner)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.updateLocationBanner.BannerNotFound',
                        [
                            'location_id'  => $location_id,
                            'banner_id'    => $banner_id,
                        ]
                    )
                );
            }

            $banner = SummitLocationBannerFactory::populate($summit, $location, $banner, $data);
            $location->validateBanner($banner);
            return $banner;
        });
    }

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $banner_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteLocationBanner(Summit $summit, $location_id, $banner_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $location_id, $banner_id) {
            $location = $summit->getLocation($location_id);

            if(is_null($location)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteLocationBanner.LocationNotFound',
                        [
                            'summit_id'    => $summit->getId(),
                            'location_id'  => $location_id,
                        ]
                    )
                );
            }

            $banner = $location->getBannerById($banner_id);

            if(is_null($banner)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteLocationBanner.BannerNotFound',
                        [
                            'location_id'  => $location_id,
                            'banner_id'    => $banner_id,
                        ]
                    )
                );
            }

            $location->removeBanner($banner);
        });
    }

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param array $metadata
     * @param $file
     * @return SummitLocationImage
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addLocationMap(Summit $summit, $location_id, array $metadata, UploadedFile $file)
    {
        $map = $this->tx_service->transaction(function () use ($summit, $location_id, $metadata, $file) {
            $max_file_size      = config('file_upload.max_file_upload_size') ;
            $allowed_extensions = ['png','jpg','jpeg','gif','pdf'];
            $location           = $summit->getLocation($location_id);

            if (is_null($location)) {
                throw new EntityNotFoundException
                (
                    trans(
                        'not_found_errors.LocationService.addLocationMap.LocationNotFound',
                        [
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            if(!$location instanceof SummitGeoLocatedLocation){
                throw new EntityNotFoundException
                (
                    trans(
                        'not_found_errors.LocationService.addLocationMap.LocationNotFound',
                        [
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            if(!in_array($file->extension(), $allowed_extensions)){
                throw new ValidationException
                (
                    trans(
                        'validation_errors.LocationService.addLocationMap.FileNotAllowedExtension',
                        [
                            'allowed_extensions' => implode(", ", $allowed_extensions),
                        ]
                    )
                );
            }

            if($file->getSize() > $max_file_size)
            {
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.LocationService.addLocationMap.FileMaxSize',
                        [
                            'max_file_size' => (($max_file_size/1024)/1024)
                        ]
                    )
                );
            }

            $uploader = new FileUploader($this->folder_repository);
            $pic      = $uploader->build($file, sprintf('summits/%s/locations/%s/maps/', $location->getSummitId(), $location->getId()), true);
            $map      = SummitLocationImageFactory::buildMap($metadata);
            $map->setPicture($pic);
            $location->addMap($map);
            return $map;
        });

        Event::fire
        (
            new LocationImageInserted
            (
                $map->getId(),
                $map->getLocationId(),
                $map->getLocation()->getSummitId(),
                $map->getClassName()
            )
        );

        return $map;
    }

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $map_id
     * @param array $metadata
     * @param $file
     * @return SummitLocationImage
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateLocationMap(Summit $summit, $location_id, $map_id, array $metadata, UploadedFile $file)
    {
        return $this->tx_service->transaction(function () use ($summit, $location_id, $map_id, $metadata, $file) {
            $max_file_size      = config('file_upload.max_file_upload_size') ;
            $allowed_extensions = ['png','jpg','jpeg','gif','pdf'];
            $location           = $summit->getLocation($location_id);

            if (is_null($location)) {
                throw new EntityNotFoundException
                (
                    trans(
                        'not_found_errors.LocationService.addLocationMap.LocationNotFound',
                        [
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            if(!$location instanceof SummitGeoLocatedLocation){
                throw new EntityNotFoundException
                (
                    trans(
                        'not_found_errors.LocationService.addLocationMap.LocationNotFound',
                        [
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            $map = $location->getMap($map_id);

            if (is_null($map)) {
                throw new EntityNotFoundException
                (
                    trans(
                        'not_found_errors.LocationService.addLocationMap.MapNotFound',
                        [
                            'map_id'      => $map_id,
                            'location_id' => $location_id,
                        ]
                    )
                );
            }

            if(!is_null($file)) {
                if (!in_array($file->extension(), $allowed_extensions)) {
                    throw new ValidationException
                    (
                        trans(
                            'validation_errors.LocationService.addLocationMap.FileNotAllowedExtension',
                            [
                                'allowed_extensions' => implode(", ", $allowed_extensions),
                            ]
                        )
                    );
                }

                if ($file->getSize() > $max_file_size) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.LocationService.addLocationMap.FileMaxSize',
                            [
                                'max_file_size' => (($max_file_size / 1024) / 1024)
                            ]
                        )
                    );
                }

                $uploader = new FileUploader($this->folder_repository);
                $pic = $uploader->build($file, sprintf('summits/%s/locations/%s/maps/', $location->getSummitId(), $location->getId()), true);
                $map->setPicture($pic);
            }

            $map = SummitLocationImageFactory::populate($map, $metadata);

            if (isset($metadata['order']) && intval($metadata['order']) != $map->getOrder()) {
                // request to update order
                $location->recalculateMapOrder($map, intval($metadata['order']));
            }

            Event::fire
            (
                new LocationImageUpdated
                (
                    $map->getId(),
                    $map->getLocationId(),
                    $map->getLocation()->getSummitId(),
                    $map->getClassName()
                )
            );

            return $map;
        });
    }

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $map_id
     * @return SummitAbstractLocation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteLocationMap(Summit $summit, $location_id, $map_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $location_id, $map_id) {

            $location = $summit->getLocation($location_id);

            if(is_null($location)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteLocationMap.LocationNotFound',
                        [
                            'summit_id'    => $summit->getId(),
                            'location_id'  => $location_id,
                        ]
                    )
                );
            }

            if(!$location instanceof SummitGeoLocatedLocation){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteLocationMap.LocationNotFound',
                        [
                            'summit_id'    => $summit->getId(),
                            'location_id'  => $location_id,
                        ]
                    )
                );
            }

            $map = $location->getMap($map_id);

            if(is_null($map)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.LocationService.deleteLocationMap.MapNotFound',
                        [
                            'location_id'  => $location_id,
                            'banner_id'    => $map_id,
                        ]
                    )
                );
            }

            Event::fire
            (
                new LocationImageDeleted
                (
                    $map->getId(),
                    $map->getLocationId(),
                    $map->getLocation()->getSummitId(),
                    $map->getClassName()
                )
            );

            $location->removeMap($map);

        });
    }
}