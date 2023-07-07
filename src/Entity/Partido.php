<?php

namespace App\Entity;

use App\Repository\PartidoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use PhpParser\Node\Stmt\Break_;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

#[ORM\Entity(repositoryClass: PartidoRepository::class)]
class Partido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\OneToMany(mappedBy: 'partido', targetEntity: Usuario::class)]
    private Collection $usuarios;

    private ?UploadedFile $uploadedFile = null;

    #[ORM\Column(length: 255)]
    private ?string $siglas = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $color = null;

    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
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
     * @return Collection<int, Usuario>
     */
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }

    public function addUsuario(Usuario $usuario): self
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios->add($usuario);
            $usuario->setPartido($this);
        }

        return $this;
    }

    public function removeUsuario(Usuario $usuario): self
    {
        if ($this->usuarios->removeElement($usuario)) {
            // set the owning side to null (unless already changed)
            if ($usuario->getPartido() === $this) {
                $usuario->setPartido(null);
            }
        }

        return $this;
    }

    public function setUploadedFile(?UploadedFile $file){
        $this->uploadedFile = $file;
        return $this;
    }

    public function getUploadedFile(): ?UploadedFile {
        return $this->uploadedFile;
    }
    public function getFileName(?KernelInterface $kernel = null): ?string {
        if (!$kernel && !$this->getUploadedFile()) return null;
        if (!$this->getUploadedFile()){
            $finder = new Finder();
            $finder->files()->in($kernel->getProjectDir()."/files/img/partidos");
            $imageFile = null;
            foreach ($finder as $file) {
                if (strpos($file->getFilename(), "[{$this->getId()}]") !== false) {
                    $imageFile = $file->getFilename();
                    break;
                }
            }
            return $imageFile;
        }else {
            return "[{$this->getId()}]{$this->getSiglas()}.{$this->getUploadedFile()->getClientOriginalExtension()}";
        }
    }

    public function getSiglas(): ?string
    {
        return $this->siglas;
    }

    public function setSiglas(string $siglas): self
    {
        $this->siglas = $siglas;

        return $this;
    }

    public function serialize(): array {
        return [
            "id" => $this->id,
            "nombre" => $this->nombre,
            "siglas" => $this->siglas
        ];
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
