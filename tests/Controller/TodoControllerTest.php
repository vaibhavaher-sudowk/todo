<?php

namespace App\Tests\Controller;

use App\Entity\ToDoItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class TodoControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient(); // ✅ only once
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        // Optional: ensure a clean DB state per test class run if using a shared DB
        // You can truncate here or rely on transaction rollbacks if using LiipTestFixtures.
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/todo');
        $this->assertResponseIsSuccessful();
        // Optionally assert presence of a heading or specific text
        // $this->assertSelectorTextContains('h1', 'ToDo');
    }

    public function testList(): void
    {
        // Seed a few ToDoItem entities
        foreach (['A', 'B', 'C'] as $i => $label) {
            $todo = new ToDoItem();
            $todo->setTitle("Seed $label");
            $todo->setPriority(3 - $i); // 3,2,1
            $todo->setCreatedDate(new \DateTime());
            $this->em->persist($todo);
        }
        $this->em->flush();

        $this->client->request('GET', '/todo/list');
        $this->assertResponseIsSuccessful();

        // Basic content presence check
        $this->assertStringContainsString('Seed', $this->client->getResponse()->getContent());
    }

    public function testCreateTodo(): void
    {
        $crawler = $this->client->request('GET', '/todo/new');
        $this->assertResponseIsSuccessful();

        // Adjust field names if your ToDoItemType uses different fields or a different prefix
        $form = $crawler->selectButton('Save')->form([
            'to_do_item[title]'    => 'Test ToDo',
            'to_do_item[priority]' => 5, // keep consistent with your form type (enum/string/int)
            // add other required fields here if your form requires them
        ]);

        $this->client->submit($form);
        $this->client->followRedirect(); // should redirect to todo_list

        $todo = $this->em->getRepository(ToDoItem::class)
            ->findOneBy(['title' => 'Test ToDo']);

        $this->assertNotNull($todo);
        $this->assertSame(5, $todo->getPriority());
        $this->assertInstanceOf(\DateTimeInterface::class, $todo->getCreatedDate());
    }

    public function testEditTodo(): void
    {
        // Arrange: create an existing ToDoItem
        $todo = new ToDoItem();
        $todo->setTitle('Old Title');
        $todo->setPriority(1);
        $todo->setCreatedDate(new \DateTime());
        $this->em->persist($todo);
        $this->em->flush();

        // Act: load edit page and submit the form
        $crawler = $this->client->request('GET', '/todo/edit/'.$todo->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form([
            'to_do_item[title]'    => 'Updated Title',
            'to_do_item[priority]' => 2,
        ]);

        $this->client->submit($form);
        $this->client->followRedirect(); // should redirect to todo_list

        // Clear EM to ensure fresh fetch
        $this->em->clear();

        $updated = $this->em->getRepository(ToDoItem::class)->find($todo->getId());
        $this->assertNotNull($updated);
        $this->assertSame('Updated Title', $updated->getTitle());
        $this->assertSame(2, $updated->getPriority());
    }

    public function testDeleteTodo(): void
    {
        // Arrange: create an item to delete
        $todo = new ToDoItem();
        $todo->setTitle('Delete Me');
        $todo->setPriority(1);
        $todo->setCreatedDate(new \DateTime());
        $this->em->persist($todo);
        $this->em->flush();

        // Generate a real CSRF token matching your controller's expectation:
        // isCsrfTokenValid('delete'.$todo->getId(), $request->request->get('_token'))
        /** @var CsrfTokenManagerInterface $csrf */
        $csrf = static::getContainer()->get(CsrfTokenManagerInterface::class);
        $token = $csrf->getToken('delete'.$todo->getId())->getValue();

        // Act: POST to delete route with token
        $this->client->request(
            'POST',
            '/todo/delete/'.$todo->getId(),
            ['_token' => $token]
        );

        // Controller redirects to list page
        $this->assertTrue(
            $this->client->getResponse()->isRedirect('/todo/list'),
            'Expected redirect to /todo/list after deletion'
        );
        $this->client->followRedirect();

        // Assert: entity removed
        $this->em->clear();
        $deleted = $this->em->getRepository(ToDoItem::class)->find($todo->getId());
        $this->assertNull($deleted);
    }
}
