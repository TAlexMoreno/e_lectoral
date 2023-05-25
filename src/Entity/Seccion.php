<?php

namespace App\Entity;

use App\Repository\SeccionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeccionRepository::class)]
class Seccion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'secciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Municipio $municipio = null;

    #[ORM\Column(length: 255)]
    public ?string $nombre = null;

    public function __construct(){}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMunicipio(): ?Municipio
    {
        return $this->municipio;
    }

    public function setMunicipio(?Municipio $municipio): self
    {
        $this->municipio = $municipio;

        return $this;
    }

    public function getNombre(): ?string
    {
        return sprintf("%04d", $this->nombre);
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }
}
