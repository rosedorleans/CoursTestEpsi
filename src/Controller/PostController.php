<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        $allPosts = $postRepository->findAll();
        $arrayCollection = array();

        foreach($allPosts as $post) {
            $arrayCollection[] = array(
                'title' => strtoupper($post->getTitle()),
                'content' => $post->getContent(),
            );
        }
        return new JsonResponse($arrayCollection);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();
            return new JsonResponse("Post créé", 200);
        }

        return new JsonResponse("Ajouter un post", 200);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET"})
     */
    public function show(EntityManagerInterface $entityManager, Request $request): Response
    {
        $post = $entityManager->getRepository('App:Post')->findOneBy(['id' => $request->get('id')]);
        $arrayCollection[] = array(
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
        );
        return new JsonResponse($arrayCollection);
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return new JsonResponse("Post modifié", 200);
        }

        return new JsonResponse("Modifier un post", 200);
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"POST"})
     */
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();

            return new JsonResponse("Post supprimé", 200);
        }

        return new JsonResponse("Supprimer un post", 200);     }
}
