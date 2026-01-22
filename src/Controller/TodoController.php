<?php

namespace App\Controller;

use App\Entity\ToDoItem;
use App\Form\ToDoItemType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class TodoController extends AbstractController
{
    #[Route('/todo', name: 'todo_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    // 🟢 LIST ALL TASKS WITH PAGINATION
    #[Route('/todo/list', name: 'todo_list')]
    public function list(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        // Build query (ordered by priority DESC)
        $query = $em->getRepository(ToDoItem::class)
            ->createQueryBuilder('t')
            ->orderBy('t.priority', 'DESC')
            ->getQuery();

        // Paginate results (10 per page)
        $todos = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // current page
            10 // items per page
        );

        return $this->render('todo/list.html.twig', [
            'todos' => $todos,
        ]);
    }

    // 🟢 CREATE NEW TASK
    #[Route('/todo/new', name: 'todo_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $todo = new ToDoItem();
        $todo->setCreatedDate(new \DateTime()); // auto-set created date

        $form = $this->createForm(ToDoItemType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($todo);
            $em->flush();

            $this->addFlash('success', 'Task created successfully!');
            return $this->redirectToRoute('todo_list');
        }

        return $this->render('todo/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 🟢 UPDATE EXISTING TASK
    #[Route('/todo/edit/{id}', name: 'todo_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $todo = $em->getRepository(ToDoItem::class)->find($id);

        if (!$todo) {
            throw $this->createNotFoundException('Task not found');
        }

        $form = $this->createForm(ToDoItemType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); // entity is already managed
            $this->addFlash('success', 'Task updated successfully!');
            return $this->redirectToRoute('todo_list');
        }

        return $this->render('todo/edit.html.twig', [
            'form' => $form->createView(),
            'todo' => $todo,
        ]);
    }

    // 🟢 DELETE TASK
    #[Route('/todo/delete/{id}', name: 'todo_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $todo = $em->getRepository(ToDoItem::class)->find($id);

        if (!$todo) {
            throw $this->createNotFoundException('Task not found');
        }

        if ($this->isCsrfTokenValid('delete'.$todo->getId(), $request->request->get('_token'))) {
            $em->remove($todo);
            $em->flush();
            $this->addFlash('success', 'Task deleted successfully!');
        }

        return $this->redirectToRoute('todo_list');
    }
}
