<?php

namespace App\Repository;

use App\Entity\Like;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 */
class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    // Compte le nombre de likes pour un post donné
    public function countLikesForPost($post)
    {
        return $this->count(['post' => $post]);
    }

    // Vérifie si un utilisateur a liké un post donné
    public function isPostLikedByUser($post, $user)
    {
        return (bool) $this->findOneBy(['post' => $post, 'user' => $user]);
    }
}
