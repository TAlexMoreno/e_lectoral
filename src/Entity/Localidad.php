<?php

namespace App\Entity;

use App\Repository\LocalidadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocalidadRepository::class)]
class Localidad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'localidades', fetch:"EAGER")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Municipio $municipio = null;

    #[ORM\Column(length: 255)]
    private ?string $clave = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

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

    public function getClave(): ?string
    {
        return $this->clave;
    }

    public function setClave(string $clave): self
    {
        $this->clave = $clave;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }
    public function serialize(){
        return [
            "id" => $this->id,
            "nombre" => $this->nombre,
            "clave" => $this->clave,
            "municipio" => $this->getMunicipio()
        ];
    }
}
