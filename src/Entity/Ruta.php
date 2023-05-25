<?php

namespace App\Entity;

use App\Repository\RutaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RutaRepository::class)]
class Ruta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255)]
    private ?string $icon = null;

    #[ORM\Column(length: 255, type: Types::SMALLINT)]
    private ?int $orden = null;

    #[ORM\Column(length: 255)]
    private ?string $minimumRole = null;

    public function __construct(?int $id = null, ?string $path = null, ?string $label = null, ?string $icon = null, ?int $orden = null, ?string $minimumRole = null){
        if ($id !== null) $this->id = $id;
        if ($path !== null) $this->path = $path;
        if ($label !== null) $this->label = $label;
        if ($icon !== null) $this->icon = $icon;
        if ($orden !== null) $this->orden = $orden;
        if ($minimumRole !== null) $this->minimumRole = $minimumRole;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getMinimumRole(): ?string
    {
        return $this->minimumRole;
    }

    public function setMinimumRole(string $minimumRole): self
    {
        $this->minimumRole = $minimumRole;

        return $this;
    }
}
