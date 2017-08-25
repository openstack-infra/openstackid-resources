<?php namespace App\Http\Controllers;
/**
 * Copyright 2017 OpenStack Foundation
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
use App\Http\Controllers\JsonController;
use App\Models\Foundation\Marketplace\IDistributionRepository;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use utils\FilterParser;
use Illuminate\Support\Facades\Input;
use utils\Filter;
use utils\PagingInfo;
use Illuminate\Support\Facades\Request;
use utils\OrderParser;
/**
 * Class DistributionsApiController
 * @package App\Http\Controllers\Apis\Marketplace
 */
final class DistributionsApiController extends AbstractCompanyServiceApiController
{

    /**
     * DistributionsApiController constructor.
     * @param IDistributionRepository $repository
     */
    public function __construct(IDistributionRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getAll()
    {
        return parent::getAll();
    }
}