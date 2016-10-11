<?php namespace repositories\main;
/**
 * Copyright 2016 OpenStack Foundation
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
use models\main\ITagRepository;
use models\main\Tag;
use repositories\SilverStripeDoctrineRepository;

/**
 * Class DoctrineTagRepository
 * @package repositories\main
 */
final class DoctrineTagRepository extends SilverStripeDoctrineRepository implements ITagRepository
{

    /**
     * @param string $tag
     * @return Tag
     */
    public function getByTag($tag)
    {
        try {
            return $this->getEntityManager()->createQueryBuilder()
                ->select("t")
                ->from(\models\main\Tag::class, "t")
                ->where('UPPER(TRIM(t.tag)) = UPPER(TRIM(:tag))')
                ->setParameter('tag', $tag)
                ->getQuery()->getOneOrNullResult();
        }
        catch(\Exception $ex){
            return null;
        }
    }
}