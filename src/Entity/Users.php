<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Comments>
     */
    #[ORM\ManyToMany(targetEntity: Comments::class, inversedBy: 'users')]
    private Collection $comments;

    // Utilisation de l'email comme identifiant unique
    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'users')]
    private Collection $author;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdAt = $this->createdAt ?? new \DateTimeImmutable('now');
        $this->author = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getEmail() ?? 'Unnamed User';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email; // Utilisation de l'email comme identifiant
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Si tu stockes des données temporaires ou sensibles, tu peux les effacer ici
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        $this->comments->removeElement($comment);

        return $this;
    }

    // Utilisation de l'email à la place de username
    public function getUsername(): ?string
    {
        return $this->getEmail(); // Retourne l'email au lieu du username
    }

    public function setUsername(string $username): static
    {
        $this->email = $username; // Définit l'email au lieu de username
        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getAuthor(): Collection
    {
        return $this->author;
    }

    public function addAuthor(Post $author): static
    {
        if (!$this->author->contains($author)) {
            $this->author->add($author);
            $author->setUsers($this);
        }

        return $this;
    }

    public function removeAuthor(Post $author): static
    {
        if ($this->author->removeElement($author)) {
            if ($author->getUsers() === $this) {
                $author->setUsers(null);
            }
        }

        return $this;
    }
}
