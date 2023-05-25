<?php

namespace App\Entity;

use App\Repository\EntidadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntidadRepository::class)]
class Entidad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $nombre = null;

    #[ORM\OneToMany(mappedBy: 'entidad', targetEntity: Distrito::class, orphanRemoval: true)]
    private Collection $distritos;

    public function __construct()
    {
        $this->distritos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Distrito>
     */
    public function getDistritos(): Collection
    {
        return $this->distritos;
    }

    public function addDistrito(Distrito $distrito): self
    {
        if (!$this->distritos->contains($distrito)) {
            $this->distritos->add($distrito);
            $distrito->setEntidad($this);
        }

        return $this;
    }

    public function removeDistrito(Distrito $distrito): self
    {
        if ($this->distritos->removeElement($distrito)) {
            // set the owning side to null (unless already changed)
            if ($distrito->getEntidad() === $this) {
                $distrito->setEntidad(null);
            }
        }

        return $this;
    }
}
