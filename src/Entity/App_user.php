<?php

namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\Common\Collections\ArrayCollection;

    #[ORM\Entity]
    class App_user
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: "bigint")]
        private int $id_user;

        #[ORM\Column(type: "string", length: 255)]
        private string $name;

        #[ORM\Column(type: "string", length: 255)]
        private string $email;

        #[ORM\Column(type: "string", length: 255)]
        private string $password;

        #[ORM\Column(type: "bigint")]
        private string $role;

        #[ORM\Column(type: "string", length: 255)]
        private string $image;

        #[ORM\OneToMany(mappedBy: "id_user1", targetEntity: Channel::class)]
        private Collection $channelsInitiated;

        #[ORM\OneToMany(mappedBy: "id_user2", targetEntity: Channel::class)]
        private Collection $channelsReceived;

        #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Applicationservice::class)]
        private Collection $serviceApplications;

        #[ORM\OneToMany(mappedBy: "id_user", targetEntity: ApplicationJob::class)]
        private Collection $jobApplications;

        #[ORM\OneToMany(mappedBy: "id_user", targetEntity: JobOffer::class)]
        private Collection $jobOffers;

        #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Serviceoffre::class)]
        private Collection $serviceOffers;

        #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Cv::class)]
        private Collection $cvs;

        #[ORM\OneToMany(mappedBy: "id_user_sender", targetEntity: Message::class)]
        private Collection $sentMessages;

        #[ORM\OneToMany(mappedBy: "id_user_sender", targetEntity: Report::class)]
        private Collection $sentReports;

        #[ORM\OneToMany(mappedBy: "id_user_target", targetEntity: Report::class)]
        private Collection $receivedReports;

        #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Test_result::class)]
        private Collection $testResults;

        #[ORM\OneToOne(mappedBy: 'user', targetEntity: Profile::class)]
        private ?Profile $profile = null;

        public function __construct()
        {
            $this->channelsInitiated = new ArrayCollection();
            $this->channelsReceived = new ArrayCollection();
            $this->serviceApplications = new ArrayCollection();
            $this->jobApplications = new ArrayCollection();
            $this->jobOffers = new ArrayCollection();
            $this->serviceOffers = new ArrayCollection();
            $this->cvs = new ArrayCollection();
            $this->sentMessages = new ArrayCollection();
            $this->sentReports = new ArrayCollection();
            $this->receivedReports = new ArrayCollection();
            $this->testResults = new ArrayCollection();
        }
        public function getIdUser()
        {
            return $this->id_user;
        }

        public function setIdUser($value)
        {
            $this->id_user = $value;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setName($value)
        {
            $this->name = $value;
        }

        public function getEmail()
        {
            return $this->email;
        }

        public function setEmail($value)
        {
            $this->email = $value;
        }

        public function getPassword()
        {
            return $this->password;
        }

        public function setPassword($value)
        {
            $this->password = $value;
        }

        public function getRole()
        {
            return $this->role;
        }

        public function setRole($value)
        {
            $this->role = $value;
        }

        public function getImage()
        {
            return $this->image;
        }

        public function setImage($value)
        {
            $this->image = $value;
        }

        // Add the following methods for the new relationships:

        public function getServiceApplications(): Collection
        {
            return $this->serviceApplications;
        }

        public function addServiceApplication(Applicationservice $application): self
        {
            if (!$this->serviceApplications->contains($application)) {
                $this->serviceApplications[] = $application;
                $application->setUser($this);
            }
            return $this;
        }

        public function removeServiceApplication(Applicationservice $application): self
        {
            if ($this->serviceApplications->removeElement($application)) {
                if ($application->getUser() === $this) {
                    $application->setUser(null);
                }
            }
            return $this;
        }

        public function getJobApplications(): Collection
        {
            return $this->jobApplications;
        }

        // Similar add/remove methods for jobApplications...

        public function getJobOffers(): Collection
        {
            return $this->jobOffers;
        }

        // Similar add/remove methods for jobOffers...

        public function getServiceOffers(): Collection
        {
            return $this->serviceOffers;
        }

        // Similar add/remove methods for serviceOffers...

        public function getCvs(): Collection
        {
            return $this->cvs;
        }

        // Similar add/remove methods for cvs...

        public function getSentMessages(): Collection
        {
            return $this->sentMessages;
        }

        // Similar add/remove methods for sentMessages...

        public function getSentReports(): Collection
        {
            return $this->sentReports;
        }

        // Similar add/remove methods for sentReports...

        public function getReceivedReports(): Collection
        {
            return $this->receivedReports;
        }

        // Similar add/remove methods for receivedReports...

        public function getTestResults(): Collection
        {
            return $this->testResults;
        }

        // Similar add/remove methods for testResults...

        public function getProfile(): ?Profile
        {
            return $this->profile;
        }

        public function setProfile(?Profile $profile): self
        {
            $this->profile = $profile;
            return $this;
        }
        public function getId(): int
{
    return $this->id_user;
}
    }