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
use App\Models\Foundation\Summit\Repositories\IRSVPTemplateRepository;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;

/**
 * Class RSVPTemplateService
 * @package App\Services\Model
 */
final class RSVPTemplateService implements IRSVPTemplateService
{
    /**
     * @var IRSVPTemplateRepository
     */
    private $rsvp_template_repository;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * RSVPTemplateService constructor.
     * @param IRSVPTemplateRepository $rsvp_template_repository
     * @param ITransactionService $tx_service
     */
    public function __construct(IRSVPTemplateRepository $rsvp_template_repository, ITransactionService $tx_service)
    {
        $this->rsvp_template_repository = $rsvp_template_repository;
        $this->tx_service = $tx_service;
    }


    /**
     * @param Summit $summit
     * @param int $template_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTemplate(Summit $summit, $template_id)
    {
       $this->tx_service->transaction(function() use($summit, $template_id){
           $template = $summit->getRSVPTemplateById($template_id);
           if(is_null($template))
               throw new EntityNotFoundException
               (
                 trans()
               );

           $summit->removeRSVPTemplate($template);
       });
    }
}