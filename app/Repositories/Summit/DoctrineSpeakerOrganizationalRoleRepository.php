<?php namespace App\Repositories\Summit;
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
use App\Models\Foundation\Summit\Repositories\ISpeakerOrganizationalRoleRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use models\summit\SpeakerOrganizationalRole;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
/**
 * Class DoctrineSpeakerOrganizationalRoleRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSpeakerOrganizationalRoleRepository
    extends SilverStripeDoctrineRepository
    implements ISpeakerOrganizationalRoleRepository
{

    /**
     * @return SpeakerOrganizationalRole[]
     */
    public function getDefaultOnes()
    {
        return $this->findBy(["is_default" => true]);
    }

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return SpeakerOrganizationalRole::class;
    }

    /**
     * @param string $role
     * @return SpeakerOrganizationalRole|null
     */
    public function getByRole($role)
    {
        $query = <<<SQL
      select * from SpeakerOrganizationalRole where
      Role = :role
SQL;
        // build rsm here
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(SpeakerOrganizationalRole::class, 'r');
        $native_query = $this->_em->createNativeQuery($query, $rsm);

        $native_query->setParameter("role", $role);

        return $native_query->getOneOrNullResult();
    }
}