<?php

namespace App\Tests\Unit\Service;

use App\Entity\ToDoItem;
use App\Service\ToDoItemManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ToDoItemManagerTest extends TestCase
{
    public function testCreatePersistsAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(ToDoItem::class));
        $em->expects($this->once())->method('flush');

        $manager = new ToDoItemManager($em);

        $item = (new ToDoItem())
            ->setTitle('T1')
            ->setDescription('D1')
            ->setStatus('OPEN')
            ->setPriority(2)
            ->setCreatedDate(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $result = $manager->create($item);

        $this->assertSame($item, $result);
        $this->assertSame('T1', $result->getTitle());
        $this->assertSame('D1', $result->getDescription());
        $this->assertSame('OPEN', $result->getStatus());
        $this->assertSame(2, $result->getPriority());
    }

    public function testUpdateFlushesOnly(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $em->expects($this->once())->method('flush');

        $manager = new ToDoItemManager($em);

        $item = (new ToDoItem())
            ->setTitle('Old')
            ->setDescription('D')
            ->setStatus('OPEN')
            ->setPriority(1)
            ->setCreatedDate(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $item->setTitle('New')->setPriority(3);

        $result = $manager->update($item);

        $this->assertSame('New', $result->getTitle());
        $this->assertSame(3, $result->getPriority());
    }

    public function testDeleteRemovesAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $item = new ToDoItem();

        $em->expects($this->once())->method('remove')->with($item);
        $em->expects($this->once())->method('flush');

        $manager = new ToDoItemManager($em);
        $manager->delete($item);

        $this->assertTrue(true);
    }

    public function testScheduleSetsDueDateAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $manager = new ToDoItemManager($em);

        $item = (new ToDoItem())
            ->setTitle('X')
            ->setStatus('OPEN')
            ->setPriority(1)
            ->setCreatedDate(new \DateTimeImmutable());

        $due = new \DateTimeImmutable('+2 days');

        $result = $manager->schedule($item, $due);

        $this->assertSame($due, $result->getDueDate());
    }
}