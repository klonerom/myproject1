<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\Tag;
use AppBundle\Form\TaskType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TaskController extends Controller
{
    /**
     * @Route("/task")
     */
    public function newAction(Request $request)
    {
        $task = new Task();

        // dummy code - this is here just so that the Task has some tags
        // otherwise, this isn't an interesting example
//        $tag1 = new Tag();
//        $tag1->setName('tag1');
//        $task->getTags()->add($tag1);
//        $tag2 = new Tag();
//        $tag2->setName('tag2');
//        $task->getTags()->add($tag2);
//
//        $tag3 = new Tag();
//        $tag3->setName('tag3');
//        $task->getTags()->add($tag3);

        // end dummy code
//dump($task);die;
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        // ... maybe do some form processing, like saving the Task and Tag objects
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();
        }

        return $this->render('task/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/task/edit/{id}")
     */
    public function editAction($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            throw $this->createNotFoundException('No task found for id '.$id);
        }

        $originalTags = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($task->getTags() as $tag) {
            $originalTags->add($tag);
        }

        $editForm = $this->createForm(TaskType::class, $task);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            // remove the relationship between the tag and the Task
            foreach ($originalTags as $tag) {
                if (false === $task->getTags()->contains($tag)) {
                    // remove the Task from the Tag
                    $tag->getTasks()->removeElement($task);

                    // if it was a many-to-one relationship, remove the relationship like this
                    // $tag->setTask(null);

                    $entityManager->persist($tag);

                    // if you wanted to delete the Tag entirely, you can also do that
                    // $entityManager->remove($tag);
                }
            }

            $entityManager->persist($task);
            $entityManager->flush();

            // redirect back to some edit page
            return $this->redirectToRoute('task_edit', array('id' => $id));
        }

        // render some form template
    }
}