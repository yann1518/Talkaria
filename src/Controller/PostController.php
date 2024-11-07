<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les posts
        $posts = $entityManager->getRepository(Post::class)->findAll();

        // Retourner la vue avec la liste des posts
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/create', name: 'app_post_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer une instance de l'entité Post
        $post = new Post();

        // Créer le formulaire lié à l'entité Post
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'utilisateur connecté
            $user = $this->getUser();
            $post->setUsers($user); // Associe l'utilisateur au post

            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Crée un nom de fichier unique pour l'image
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Déplace le fichier dans le répertoire de stockage
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Répertoire défini dans services.yaml
                        $newFilename
                    );
                    // Enregistre le nom du fichier dans l'entité
                    $post->setImageFilename($newFilename);
                } catch (FileException $e) {
                    // Gérer l'exception en cas d'erreur de téléchargement
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            // Sauvegarde du post dans la base de données
            $entityManager->persist($post);
            $entityManager->flush();

            // Rediriger après la création du post
            return $this->redirectToRoute('app_home'); // Remplacez par la route souhaitée
        }

        // Affiche le formulaire de création du post
        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}', name: 'app_post_show')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupère le post avec l'ID donné
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        // Affiche les détails du post
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_post_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupère le post à éditer
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        // Crée le formulaire de modification
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde les modifications dans la base de données
            $entityManager->flush();

            // Redirige après la modification
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        // Affiche le formulaire d'édition du post
        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete')]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupère le post à supprimer
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        // Supprime le post de la base de données
        $entityManager->remove($post);
        $entityManager->flush();

        // Redirige après la suppression
        return $this->redirectToRoute('app_home'); // Remplacez par la route souhaitée
    }
}
