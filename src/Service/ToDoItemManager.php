<?php

namespace App\Service;

use App\Entity\ToDoItem;
use Doctrine\ORM\EntityManagerInterface;

class ToDoItemManager
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function create(ToDoItem $item): ToDoItem
    {
        $this->em->persist($item);
        $this->em->flush();
        return $item;
    }

    public function update(ToDoItem $item): ToDoItem
    {
        $this->em->flush();
        return $item;
    }

    public function delete(ToDoItem $item): void
    {
        $this->em->remove($item);
        $this->em->flush();
    }

    public function schedule(ToDoItem $item, \DateTimeInterface $due): ToDoItem
    {
        $item->setDueDate($due);
        $this->em->flush();
        return $item;
    }
}