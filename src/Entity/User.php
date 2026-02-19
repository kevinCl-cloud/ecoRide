<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;



#[UniqueEntity(
    fields: ['email'],
    message: 'Cet email est déjà utilisé.'
)]
#[UniqueEntity(
    fields: ['pseudo'],
    message: 'Ce pseudo est déjà utilisé.'
)]

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, unique:true)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 100, unique:true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column]
    private ?int $credits = 0;

    #[ORM\Column]
    private ?bool $isDriver = false;

    #[ORM\Column]
    private ?bool $isPassenger = true;

    #[ORM\Column]
    private ?bool $isSupended = false;

    #[ORM\ManyToOne(inversedBy: 'user')]
    #[ORM\JoinColumn(name: "Role", referencedColumnName: "id", nullable: true)]
    private ?Role $role = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Vehicule>
     */
    #[ORM\OneToMany(targetEntity: Vehicule::class, mappedBy: 'idDriver', orphanRemoval: true)]
    private Collection $vehicules;

    /**
     * @var Collection<int, Covoiturage>
     */
    #[ORM\OneToMany(targetEntity: Covoiturage::class, mappedBy: 'idDriver', orphanRemoval: true)]
    private Collection $covoiturages;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'idUser', orphanRemoval: true)]
    private Collection $reservations;

    /**
     * @var Collection<int, CreditTransaction>
     */
    #[ORM\OneToMany(targetEntity: CreditTransaction::class, mappedBy: 'idUser', orphanRemoval: true)]
    private Collection $creditTransactions;

    #[ORM\Column(nullable: true)]
    private ?bool $smokingAllowed = null;

    #[ORM\Column(nullable: true)]
    private ?bool $petsAllowed = null;

    public function __construct()
    {
        $this->vehicules = new ArrayCollection();
        $this->covoiturages = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->creditTransactions = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

   public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    public function isDriver(): ?bool
    {
        return $this->isDriver;
    }

    public function setIsDriver(bool $isDriver): static
    {
        $this->isDriver = $isDriver;

        return $this;
    }

    public function isPassenger(): ?bool
    {
        return $this->isPassenger;
    }

    public function setIsPassenger(bool $isPassenger): static
    {
        $this->isPassenger = $isPassenger;

        return $this;
    }

    public function isSupended(): ?bool
    {
        return $this->isSupended;
    }

    public function setIsSupended(bool $isSupended): static
    {
        $this->isSupended = $isSupended;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        
        $roles = ['ROLE_USER'];

        if ($this->role && $this->role->getLibel()) {
            $roles[] = 'ROLE_' . strtoupper($this->role->getLibel());
        }

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Vehicule>
     */
    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }

    public function addVehicule(Vehicule $vehicule): static
    {
        if (!$this->vehicules->contains($vehicule)) {
            $this->vehicules->add($vehicule);
            $vehicule->setIdDriver($this);
        }

        return $this;
    }

    public function removeVehicule(Vehicule $vehicule): static
    {
        if ($this->vehicules->removeElement($vehicule)) {
            // set the owning side to null (unless already changed)
            if ($vehicule->getIdDriver() === $this) {
                $vehicule->setIdDriver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Covoiturage>
     */
    public function getCovoiturages(): Collection
    {
        return $this->covoiturages;
    }

    public function addCovoiturage(Covoiturage $covoiturage): static
    {
        if (!$this->covoiturages->contains($covoiturage)) {
            $this->covoiturages->add($covoiturage);
            $covoiturage->setIdDriver($this);
        }

        return $this;
    }

    public function removeCovoiturage(Covoiturage $covoiturage): static
    {
        if ($this->covoiturages->removeElement($covoiturage)) {
            // set the owning side to null (unless already changed)
            if ($covoiturage->getIdDriver() === $this) {
                $covoiturage->setIdDriver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setIdUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getIdUser() === $this) {
                $reservation->setIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CreditTransaction>
     */
    public function getCreditTransactions(): Collection
    {
        return $this->creditTransactions;
    }

    public function addCreditTransaction(CreditTransaction $creditTransaction): static
    {
        if (!$this->creditTransactions->contains($creditTransaction)) {
            $this->creditTransactions->add($creditTransaction);
            $creditTransaction->setIdUser($this);
        }

        return $this;
    }

    public function removeCreditTransaction(CreditTransaction $creditTransaction): static
    {
        if ($this->creditTransactions->removeElement($creditTransaction)) {
            // set the owning side to null (unless already changed)
            if ($creditTransaction->getIdUser() === $this) {
                $creditTransaction->setIdUser(null);
            }
        }

        return $this;
    }

    public function isSmokingAllowed(): ?bool
    {
        return $this->smokingAllowed;
    }

    public function setSmokingAllowed(?bool $smokingAllowed): static
    {
        $this->smokingAllowed = $smokingAllowed;

        return $this;
    }

    public function isPetsAllowed(): ?bool
    {
        return $this->petsAllowed;
    }

    public function setPetsAllowed(?bool $petsAllowed): static
    {
        $this->petsAllowed = $petsAllowed;

        return $this;
    }
}
