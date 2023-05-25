<?php

namespace App\Entity;

use App\Enums\EstadosUsuario;
use App\Repository\UsuarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastIP = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastAccess = null;

    #[ORM\Column(type: Types::SMALLINT, options: ["comment" => "0: just created, 1: operative, 2: lost access, 3: blocked"])]
    private ?int $estatus = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recuperationCode = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'usuario', cascade: ['persist', 'remove'])]
    private ?Promotor $promotor = null;

    #[ORM\Column(length: 255)]
    private ?string $correo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastIP(): ?string
    {
        return $this->lastIP;
    }

    public function setLastIP(?string $lastIP): self
    {
        $this->lastIP = $lastIP;

        return $this;
    }

    public function getLastAccess(): ?\DateTimeInterface
    {
        return $this->lastAccess;
    }

    public function setLastAccess(?\DateTimeInterface $lastAccess): self
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    public function getEstatus(): ?EstadosUsuario
    {
        return EstadosUsuario::tryFrom($this->estatus);
    }

    public function setEstatus(EstadosUsuario $estatus): self
    {
        $this->estatus = $estatus->value;

        return $this;
    }

    public function getRecuperationCode(): ?string
    {
        return $this->recuperationCode;
    }

    public function setRecuperationCode(?string $recuperationCode): self
    {
        $this->recuperationCode = $recuperationCode;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPromotor(): ?Promotor
    {
        return $this->promotor;
    }

    public function setPromotor(Promotor $promotor): self
    {
        // set the owning side of the relation if necessary
        if ($promotor->getUsuario() !== $this) {
            $promotor->setUsuario($this);
        }

        $this->promotor = $promotor;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }
}
