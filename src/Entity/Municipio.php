<?php

namespace App\Entity;

use App\Repository\MunicipioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MunicipioRepository::class)]
class Municipio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'municipios')]
    #[ORM\JoinColumn(nullable: false)]
    public ?Distrito $distrito = null;

    #[ORM\Column(length: 255)]
    public ?string $nombre = null;

    #[ORM\OneToMany(mappedBy: 'municipio', targetEntity: Seccion::class, orphanRemoval: true)]
    private Collection $secciones;

    #[ORM\OneToMany(mappedBy: 'municipio', targetEntity: Localidad::class, orphanRemoval: true)]
    private Collection $localidades;

    public function __construct()
    {
        $this->secciones = new ArrayCollection();
        $this->localidades = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDistrito(): ?Distrito
    {
        return $this->distrito;
    }

    public function setDistrito(?Distrito $distrito): self
    {
        $this->distrito = $distrito;

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

    /**
     * @return Collection<int, Seccion>
     */
    public function getSecciones(): Collection
    {
        return $this->secciones;
    }

    public function addSeccione(Seccion $seccione): self
    {
        if (!$this->secciones->contains($seccione)) {
            $this->secciones->add($seccione);
            $seccione->setMunicipio($this);
        }

        return $this;
    }

    public function removeSeccione(Seccion $seccione): self
    {
        if ($this->secciones->removeElement($seccione)) {
            // set the owning side to null (unless already changed)
            if ($seccione->getMunicipio() === $this) {
                $seccione->setMunicipio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Localidad>
     */
    public function getLocalidades(): Collection
    {
        return $this->localidades;
    }

    public function addLocalidade(Localidad $localidade): self
    {
        if (!$this->localidades->contains($localidade)) {
            $this->localidades->add($localidade);
            $localidade->setMunicipio($this);
        }

        return $this;
    }

    public function removeLocalidade(Localidad $localidade): self
    {
        if ($this->localidades->removeElement($localidade)) {
            // set the owning side to null (unless already changed)
            if ($localidade->getMunicipio() === $this) {
                $localidade->setMunicipio(null);
            }
        }

        return $this;
    }
}
