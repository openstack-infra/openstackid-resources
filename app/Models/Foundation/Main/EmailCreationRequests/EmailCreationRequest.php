<?php namespace models\main;
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
use Doctrine\ORM\Mapping AS ORM;
use models\utils\SilverstripeBaseModel;
use DateTime;
/**
 * @ORM\Entity(repositoryClass="repositories\main\DoctrineEmailCreationRequestRepository")
 * @ORM\Table(name="EmailCreationRequest")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"EmailCreationRequest" = "EmailCreationRequest",
 *     "SpeakerCreationEmailCreationRequest" = "SpeakerCreationEmailCreationRequest",
 *     "MemberPromoCodeEmailCreationRequest"= "MemberPromoCodeEmailCreationRequest",
 *     "SpeakerSelectionAnnouncementEmailCreationRequest" = "SpeakerSelectionAnnouncementEmailCreationRequest"})
 * Class EmailCreationRequest
 * @package models\main
 */
class EmailCreationRequest extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="TemplateName", type="string")
     * @var string
     */
    protected $template_name;

    /**
     * @ORM\Column(name="Processed", type="boolean")
     * @var bool
     */
    protected $processed;

    /**
     * @ORM\Column(name="ProcessedDate", type="datetime")
     * @var DateTime
     */
    protected $processed_date;

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template_name;
    }

    /**
     * @param string $template_name
     */
    public function setTemplateName($template_name)
    {
        $this->template_name = $template_name;
    }

    /**
     * @return bool
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * @param bool $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return DateTime
     */
    public function getProcessedDate()
    {
        return $this->processed_date;
    }

    /**
     * @param DateTime $processed_date
     */
    public function setProcessedDate($processed_date)
    {
        $this->processed_date = $processed_date;
    }

    public function __construct()
    {
        $this->processed = false;
        parent::__construct();
    }
}