<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="task_list")
     */
    public function list(): Response
    {
        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository(Task::class)->findAll()]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function create(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function edit(Task $task, Request $request): Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleState(Task $task): Response
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();
        
        $message = sprintf('La tâche %s a bien été marquée comme non terminée.', $task->getTitle());
        
        if(true === $task->isDone()) {
            $message = sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle());
        }
        
        $this->addFlash('success',$message);

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function delete(Task $task): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
