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
use App\Models\Foundation\Summit\Factories\SummitLocationFactory;
use App\Models\Foundation\Summit\Repositories\ISummitLocationRepository;
use App\Services\Apis\AddressInfo;
use App\Services\Apis\GeoCodingApiException;
use App\Services\Apis\GeoCoordinatesInfo;
use App\Services\Apis\IGeoCodingAPI;
use App\Services\Model\Strategies\GeoLocation\GeoLocationStrategyFactory;
use Illuminate\Support\Facades\Log;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;
use models\summit\SummitAbstractLocation;
use models\summit\SummitGeoLocatedLocation;
use models\summit\SummitVenue;
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
     * @param IGeoCodingAPI $geo_coding_api
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitLocationRepository $location_repository,
        IGeoCodingAPI $geo_coding_api,
        ITransactionService $tx_service
    )
    {
        $this->location_repository = $location_repository;
        $this->geo_coding_api      = $geo_coding_api;
        $this->tx_service          = $tx_service;
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
       return $this->tx_service->transaction(function() use($summit, $data){

           $old_location = $summit->getLocationByName(trim($data['name']));

            if(!is_null($old_location)){
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

            if(is_null($location)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.LocationService.addLocation.InvalidClassName'
                    )
                );
            }

            if($location instanceof SummitGeoLocatedLocation) {
                try {
                    $geo_location_strategy = GeoLocationStrategyFactory::build($location);
                    $geo_location_strategy->doGeoLocation($location, $this->geo_coding_api);
                }
                catch (GeoCodingApiException $ex1){
                    Log::warning($ex1->getMessage());
                    $validation_msg = trans('validation_errors.LocationService.addLocation.geoCodingGenericError');
                    switch ($ex1->getStatus()){
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
                }
                catch(\Exception $ex){
                    Log::warning($ex->getMessage());
                    throw $ex;
                }
            }

            $summit->addLocation($location);
            return $location;
       });
    }
}