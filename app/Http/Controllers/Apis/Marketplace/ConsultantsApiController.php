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
use App\Models\Foundation\Marketplace\IConsultantRepository;

/**
 * Class ConsultantsApiController
 * @package App\Http\Controllers
 */
final class ConsultantsApiController extends AbstractCompanyServiceApiController
{

    /**
     * ConsultansApiController constructor.
     * @param IConsultantRepository $repository
     */
    public function __construct(IConsultantRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getAll()
    {
        return parent::getAll();
    }
}