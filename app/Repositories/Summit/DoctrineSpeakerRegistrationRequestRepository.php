<?php namespace App\Repositories\Summit;
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
use App\Repositories\SilverStripeDoctrineRepository;
use models\summit\ISpeakerRegistrationRequestRepository;
use models\summit\SpeakerRegistrationRequest;

/**
 * Class DoctrineSpeakerRegistrationRequestRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSpeakerRegistrationRequestRepository
    extends SilverStripeDoctrineRepository
    implements ISpeakerRegistrationRequestRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return SpeakerRegistrationRequest::class;
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function existByHash($hash)
    {
       return $this->getByHash($hash) != null;
    }

    /**
     * @param string $hash
     * @return SpeakerRegistrationRequest
     */
    public function getByHash($hash)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select("r")
            ->from(SpeakerRegistrationRequest::class, "r")
            ->where("r.confirmation_hash = :confirmation_hash")
            ->setParameter("confirmation_hash", trim($hash))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $email
     * @return bool
     */
    public function existByEmail($email)
    {
        return $this->getByEmail($email) != null;
    }

    /**
     * @param string $email
     * @return SpeakerRegistrationRequest
     */
    public function getByEmail($email)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select("r")
            ->from(SpeakerRegistrationRequest::class, "r")
            ->where("r.email = :email")
            ->setParameter("email", trim($email))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}