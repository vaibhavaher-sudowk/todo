<?php

namespace App\Service;

use App\Entity\ToDoItem;
use Doctrine\ORM\EntityManagerInterface;

class ToDoItemManager
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Persist a new ToDoItem and flush.
     */
    public function create(ToDoItem $item): ToDoItem
    {
        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }

    /**
     * Flush changes to an existing managed ToDoItem.
     * Caller should mutate the entity before calling update().
     */
    public function update(ToDoItem $item): ToDoItem
    {
        $this->em->flush();
        return $item;
    }

    /**
     * Remove a ToDoItem and flush.
     */
    public function delete(ToDoItem $item): void
    {
        $this->em->remove($item);
        $this->em->flush();
    }

    /**
     * Set a due date and flush.
     */
    public function schedule(ToDoItem $item, \DateTimeInterface $due): ToDoItem
    {
        $item->setDueDate($due);
        $this->em->flush();

        return $item;
    }
}