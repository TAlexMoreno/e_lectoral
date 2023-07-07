<?php

namespace App\Entity;

use App\Repository\SesionesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SesionesRepository::class)]
class Sesiones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
