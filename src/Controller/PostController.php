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
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les posts
        $posts = $entityManager->getRepository(Post::class)->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/create-or-edit/{id?}', name: 'app_post_create_or_edit')]
    public function createOrEdit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, int $id = null): Response
    {
        // Si un ID est fourni, essayez de récupérer le post existant
        $post = $id ? $entityManager->getRepository(Post::class)->find($id) : new Post();
    
        if ($id && !$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }
    
        // Crée ou pré-remplit le formulaire avec le post existant
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$post->getId()) {
                // Associer l'utilisateur connecté si nouveau post
                $post->setUsers($this->getUser());
            }
    
            // Gérer le téléchargement de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $post->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }
    
            // Générer un slug unique
            $slug = $this->generateUniqueSlug($post->getTitle(), $entityManager, $slugger);
            $post->setSlug($slug);
    
            $entityManager->persist($post);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId(), 'slug' => $post->getSlug()]);
        }
    
        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
            'is_editing' => $id !== null,
        ]);
    }
    
    #[Route('/post/{id}/{slug}', name: 'app_post_show')]
    public function show(int $id, string $slug, EntityManagerInterface $entityManager): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post || $post->getSlug() !== $slug) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_post_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {

        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post || $post->getSlug() !== $slug) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }
        

        if ($post->getUsers() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce post.');
        }


        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slug = $this->generateUniqueSlug($post->getTitle(), $entityManager, $slugger);
            $post->setSlug($slug);


            $entityManager->flush();

            // Rediriger vers la page du post modifié
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId(), 'slug' => $post->getSlug()]);
        }

        // Afficher le formulaire de modification
        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete')]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le post à supprimer
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        // Supprimer le post
        $entityManager->remove($post);
        $entityManager->flush();

        // Rediriger après la suppression
        return $this->redirectToRoute('app_home');
    }

    private function generateUniqueSlug(string $title, EntityManagerInterface $entityManager, SluggerInterface $slugger): string
    {
        // Générer un slug basé sur le titre
        $slug = strtolower($slugger->slug($title)->toString());
        $originalSlug = $slug;

        // Vérifier si le slug est déjà pris et ajouter un suffixe pour garantir l'unicité
        $i = 1;
        while ($entityManager->getRepository(Post::class)->findOneBy(['slug' => $slug])) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
