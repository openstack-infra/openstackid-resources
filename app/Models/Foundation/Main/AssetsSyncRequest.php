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
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="repositories\main\DoctrineAssetsSyncRequestRepository")
 * @ORM\Table(name="AssetsSyncRequest")
 * Class File
 * @package models\main
 */
class AssetsSyncRequest extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="`From`", type="string")
     */
    private $from;

    /**
     * @ORM\Column(name="`To`", type="string")
     */
    private $to;

    /**
     * @ORM\Column(name="Processed", type="boolean")
     */
    private $processed;

    /**
     * @ORM\Column(name="ProcessedDate", type="datetime")
     */
    private $processed_date;

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param string $processed
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
}