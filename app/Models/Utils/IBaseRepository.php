<?php namespace models\utils;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Interface IBaseRepository
 */
interface IBaseRepository
{
    /**
     * @param int $id
     * @return IEntity
     */
    public function getById($id);

    /**
     * @param IEntity $entity
     * @return void
     */
    public function add($entity);

    /**
     * @param IEntity $entity
     * @return void
     */
    public function delete($entity);

    /**
     * @return IEntity[]
     */
    public function getAll();

    /**
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getAllByPage(PagingInfo $paging_info, Filter $filter = null, Order $order = null);

}