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
use App\Models\Foundation\Summit\Events\RSVP\RSVPQuestionTemplate;
use App\Models\Foundation\Summit\Factories\SummitRSVPTemplateQuestionFactory;
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
                 trans
                 (
                     'not_found_errors.RSVPTemplateService.deleteTemplate.TemplateNotFound',
                     [
                         'summit_id'   => $summit->getId(),
                         'template_id' => $template_id,
                     ]
                 )
               );

           $summit->removeRSVPTemplate($template);
       });
    }

    /**
     * @param Summit $summit
     * @param $template_id
     * @param array $data
     * @return RSVPQuestionTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addQuestion(Summit $summit, $template_id, array $data)
    {
        return $this->tx_service->transaction(function() use($summit, $template_id, $data){

            $template = $summit->getRSVPTemplateById($template_id);

            if(is_null($template))
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.RSVPTemplateService.addQuestion.TemplateNotFound',
                        [
                            'summit_id'   => $summit->getId(),
                            'template_id' => $template_id,
                        ]
                    )
                );

            $former_question = $template->getQuestionByName($data['name']);
            if(!is_null($former_question)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.RSVPTemplateService.addQuestion.QuestionNameAlreadyExists',
                        [
                            'template_id' => $template_id,
                            'name'        => $data['name']
                        ]
                    )
                );
            }

            $question = SummitRSVPTemplateQuestionFactory::build($data);

            $template->addQuestion($question);

            return $question;
        });
    }

    /**
     * @param Summit $summit
     * @param int $template_id
     * @param int $question_id
     * @param array $data
     * @return RSVPQuestionTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateQuestion(Summit $summit, $template_id, $question_id, array $data)
    {
        return $this->tx_service->transaction(function() use($summit, $template_id, $question_id, $data){

            $template = $summit->getRSVPTemplateById($template_id);

            if(is_null($template))
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.RSVPTemplateService.updateQuestion.TemplateNotFound',
                        [
                            'summit_id'   => $summit->getId(),
                            'template_id' => $template_id,
                        ]
                    )
                );

            $question = $template->getQuestionById($question_id);
            if(is_null($question))
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.RSVPTemplateService.updateQuestion.QuestionNotFound',
                        [
                            'summit_id'   => $summit->getId(),
                            'template_id' => $template_id,
                            'question_id' => $question_id,
                        ]
                    )
                );

            if(isset($data['name'])) {
                $former_question = $template->getQuestionByName($data['name']);
                if (!is_null($former_question) && $former_question->getId() != $question_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.RSVPTemplateService.updateQuestion.QuestionNameAlreadyExists',
                            [
                                'template_id' => $template_id,
                                'name' => $data['name']
                            ]
                        )
                    );
                }
            }

            return SummitRSVPTemplateQuestionFactory::populate($question, $data);

        });
    }
}