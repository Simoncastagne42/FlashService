<?php

namespace App\Entity;

use App\Repository\ProfessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessionRepository::class)]
class Profession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Professional>
     */
    #[ORM\OneToMany(mappedBy: 'profession', targetEntity: Professional::class, cascade: ['persist', 'remove'])]
    private Collection $professional;

    public function __construct()
    {
        $this->professional = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProfessional(): ?Professional
    {
        return $this->professional;
    }

    public function setProfessional(Professional $professional): static
    {
        // set the owning side of the relation if necessary
        if ($professional->getProfession() !== $this) {
            $professional->setProfession($this);
        }

        $this->professional = $professional;

        return $this;
    }
}
