<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ToDoItem;
use PHPUnit\Framework\TestCase;

class ToDoItemTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $item = new ToDoItem();

        $created = new \DateTimeImmutable('2024-01-01 10:00:00');
        $due     = new \DateTimeImmutable('2024-01-10 18:30:00');

        $item
            ->setTitle('Title A')
            ->setDescription('Desc A')
            ->setStatus('OPEN')
            ->setPriority(3)
            ->setCreatedDate($created)
            ->setDueDate($due);

        $this->assertSame('Title A', $item->getTitle());
        $this->assertSame('Desc A', $item->getDescription());
        $this->assertSame('OPEN', $item->getStatus());
        $this->assertSame(3, $item->getPriority());
        $this->assertSame($created, $item->getCreatedDate());
        $this->assertSame($due, $item->getDueDate());
    }
}