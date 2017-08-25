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
use App\Models\Foundation\Marketplace\IApplianceRepository;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use utils\Filter;
use utils\FilterParser;
use utils\OrderParser;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use utils\PagingInfo;
/**
 * Class AppliancesApiController
 * @package App\Http\Controllers
 */
final class AppliancesApiController extends AbstractCompanyServiceApiController
{

    /**
     * AppliancesApiController constructor.
     * @param IApplianceRepository $repository
     */
    public function __construct(IApplianceRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getAll()
    {
        return parent::getAll();
    }
}