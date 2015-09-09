<?php namespace models\utils;

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

}