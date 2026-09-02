<?php

declare(strict_types=1);

namespace App\Doctrine;

class Change
{
    /**
     * @var string
     */
    final public const ACTION_CREATE = 'create';
    /**
     * @var string
     */
    final public const ACTION_UPDATE = 'update';
    /**
     * @var string
     */
    final public const ACTION_DELETE = 'delete';

    private array $changeSet = [];

    private string $action;

    private object $entity;

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return Change
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param object $entity
     *
     * @return Change
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * @return Change
     */
    public function setChangeSet(array $changeSet)
    {
        $this->changeSet = $changeSet;

        return $this;
    }
}
