<?php namespace App\Repositories;
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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use LaravelDoctrine\ORM\Facades\Registry;use models\utils\IBaseRepository;
use models\utils\IEntity;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class DoctrineRepository
 * @package App\Repositories
 */
abstract class DoctrineRepository extends EntityRepository implements IBaseRepository
{
    protected static $em_name = 'default';
    /**
     * Initializes a new <tt>EntityRepository</tt>.
     *
     * @param EntityManager         $em    The EntityManager to use.
     * @param ClassMetadata $class The class descriptor.
     */
    public function __construct($em, ClassMetadata $class)
    {
        $em = Registry::getManager(static::$em_name);
        parent::__construct($em, $class);
    }

    public function getById($id)
    {
        return $this->find($id);
    }

    /**
     * @param IEntity $entity
     * @return void
     */
    public function add($entity)
    {
        $this->_em->persist($entity);
    }

    /**
     * @param IEntity $entity
     * @return void
     */
    public function delete($entity)
    {
        $this->_em->remove($entity);
    }

    /**
     * @return IEntity[]
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @return string
     */
    protected abstract function getBaseEntity();

    /**
     * @return array
     */
    protected abstract function getFilterMappings();

    /**
     * @return array
     */
    protected abstract function getOrderMappings();

    /**
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    protected abstract function applyExtraFilters(QueryBuilder $query);

    /**
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getAllByPage(PagingInfo $paging_info, Filter $filter = null, Order $order = null){

        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("e")
            ->from($this->getBaseEntity(), "e");

        $query = $this->applyExtraFilters($query);

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if(!is_null($order)){
            $order->apply2Query($query, $this->getOrderMappings());
        }

        $query= $query
            ->setFirstResult($paging_info->getOffset())
            ->setMaxResults($paging_info->getPerPage());

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $total     = $paginator->count();
        $data      = array();

        foreach($paginator as $entity)
            array_push($data, $entity);

        return new PagingResponse
        (
            $total,
            $paging_info->getPerPage(),
            $paging_info->getCurrentPage(),
            $paging_info->getLastPage($total),
            $data
        );
    }

}