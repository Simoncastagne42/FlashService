<?php

namespace App\Entity;

use App\Repository\ProfessionalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessionalRepository::class)]
class Professional
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $compagnyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adressCompagny = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cityCompagny = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zipCodeCompagny = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneCompagny = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siret = null;

    #[ORM\OneToOne(inversedBy: 'professional', targetEntity: User::class, cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'professional', targetEntity: Profession::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Profession $profession = null;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'professional')]
    private Collection $services;

    /**
     * @var Collection<int, TimeSlot>
     */
    #[ORM\OneToMany(targetEntity: TimeSlot::class, mappedBy: 'professional')]
    private Collection $timeSlots;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->timeSlots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompagnyName(): ?string
    {
        return $this->compagnyName;
    }

    public function setCompagnyName(?string $compagnyName): static
    {
        $this->compagnyName = $compagnyName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAdressCompagny(): ?string
    {
        return $this->adressCompagny;
    }

    public function setAdressCompagny(?string $adressCompagny): static
    {
        $this->adressCompagny = $adressCompagny;

        return $this;
    }

    public function getCityCompagny(): ?string
    {
        return $this->cityCompagny;
    }

    public function setCityCompagny(?string $cityCompagny): static
    {
        $this->cityCompagny = $cityCompagny;

        return $this;
    }

    public function getZipCodeCompagny(): ?string
    {
        return $this->zipCodeCompagny;
    }

    public function setZipCodeCompagny(?string $zipCodeCompagny): static
    {
        $this->zipCodeCompagny = $zipCodeCompagny;

        return $this;
    }

    public function getPhoneCompagny(): ?string
    {
        return $this->phoneCompagny;
    }

    public function setPhoneCompagny(?string $phoneCompagny): static
    {
        $this->phoneCompagny = $phoneCompagny;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProfession(): ?Profession
    {
        return $this->profession;
    }

    public function setProfession(Profession $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setProfessional($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getProfessional() === $this) {
                $service->setProfessional(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TimeSlot>
     */
    public function getTimeSlots(): Collection
    {
        return $this->timeSlots;
    }

    public function addTimeSlot(TimeSlot $timeSlot): static
    {
        if (!$this->timeSlots->contains($timeSlot)) {
            $this->timeSlots->add($timeSlot);
            $timeSlot->setProfessional($this);
        }

        return $this;
    }

    public function removeTimeSlot(TimeSlot $timeSlot): static
    {
        if ($this->timeSlots->removeElement($timeSlot)) {
            // set the owning side to null (unless already changed)
            if ($timeSlot->getProfessional() === $this) {
                $timeSlot->setProfessional(null);
            }
        }

        return $this;
    }

    public function __toString(): string
{
    return $this->compagnyName ?? 'Professionnel';
}
}
