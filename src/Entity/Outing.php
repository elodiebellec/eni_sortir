<?php

namespace App\Entity;

use App\Repository\OutingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=OutingRepository::class)
 */
class Outing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Merci de saisir un nom pour la sortie.")
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="Le nom saisi est trop court (minimum 2 caractères)",
     *     maxMessage="Le nom saisi est trop long (maximum 50 caractères)"
     * )
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @Assert\GreaterThan("today", message="La date de la sortie doit être postérieure à la date du jour.")
     * @Assert\NotBlank(message="Merci de saisir une date et heure de sortie.")
     * @ORM\Column(type="datetime")
     */
    private $dateBegin;

    /**
     * @Assert\Type (type="integer", message="Merci de saisir une durée sous format numérique et sans virgule.")
     * @Assert\Range (
     *     min=10,
     *     max=10000,
     *     minMessage="La durée est de 10 mn minimum.",
     *     maxMessage="La durée est de 10 000 mn maximum."
     *  )
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @Assert\NotBlank(message="Merci de saisir une date limite d'inscription.")
     * @Assert\Type("DateTimeInterface")
     * @Assert\LessThan(propertyPath="dateBegin", message="La date limite d'inscription ne doit pas être postérieure à la date de la sortie.")
     * @ORM\Column(type="datetime")
     */
    private $dateEnd;

    /**
     * @Assert\Type (type="integer", message="Le nombre de place doit être un entier.")
     * @Assert\NotBlank(message="Merci de saisir un nombre de place.")
     * @Assert\Range (
     *     min=2,
     *     max=1000000,
     *     minMessage="Le nombre minimum de places est 2.",
     *     maxMessage="Le nombre maximum de places est 1 million, au delà nous n'aurons pas la place de loger tout le monde."
     *  )
     * @ORM\Column(type="integer")
     */
    private $maxRegistration;

    /**
     * @Assert\Length(
     *     max=255,
     *     maxMessage="La description ne doit pas dépasser 255 caratères.")
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @Assert\NotBlank(message="Merci de sélectionner un lieu.")
     * @ORM\ManyToOne(targetEntity=Location::class, inversedBy="outings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity=State::class, inversedBy="outings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="outings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class, inversedBy="outingsAsPlanner")
     * @ORM\JoinColumn(nullable=false)
     */
    private $planner;

    /**
     * @ORM\ManyToMany(targetEntity=Participant::class, inversedBy="outings")
     */
    private $participants;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cancellationReason;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->dateBegin;
    }

    public function setDateBegin(\DateTimeInterface $dateBegin): self
    {
        $this->dateBegin = $dateBegin;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getMaxRegistration(): ?int
    {
        return $this->maxRegistration;
    }

    public function setMaxRegistration(int $maxRegistration): self
    {
        $this->maxRegistration = $maxRegistration;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function setCancellationReason(?string $cancellationReason): self
    {
        $this->cancellationReason = $cancellationReason;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getPlanner(): ?Participant
    {
        return $this->planner;
    }

    public function setPlanner(?Participant $planner): self
    {
        $this->planner = $planner;

        return $this;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }
}
