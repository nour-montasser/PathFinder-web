<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Channel;
use App\Entity\Applicationservice;
use App\Entity\Application_job;
use App\Entity\Job_offer;
use App\Entity\Serviceoffre;
use App\Entity\Cv;
use App\Entity\Message;
use App\Entity\Report;
use App\Entity\Test_result;
use App\Entity\Profile;

#[ORM\Entity]
class App_user
{
    #[ORM\Id]
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

    // Channels initiated by the user
    #[ORM\OneToMany(mappedBy: "initiator", targetEntity: Channel::class)]
    private Collection $channelsInitiated;
    
    // Channels where the user is receiver
    #[ORM\OneToMany(mappedBy: "receiver", targetEntity: Channel::class)]
    private Collection $channelsReceived;
    
    // Service applications: owning side in Applicationservice must use inversedBy="serviceApplications"
    #[ORM\OneToMany(mappedBy: "user", targetEntity: Applicationservice::class)]
    private Collection $serviceApplications;
    
    // Job applications
    #[ORM\OneToMany(mappedBy: "user", targetEntity: Application_job::class)]
    private Collection $jobApplications;
    
    // Job offers
    #[ORM\OneToMany(mappedBy: "user", targetEntity: Job_offer::class)]
    private Collection $jobOffers;
    
    // Service offers
    #[ORM\OneToMany(mappedBy: "user", targetEntity: Serviceoffre::class)]
    private Collection $serviceOffers;
    
    // CVs
    #[ORM\OneToMany(mappedBy: "user", targetEntity: Cv::class)]
    private Collection $cvs;
    
    #[ORM\OneToMany(mappedBy: "sender", targetEntity: Message::class)]
    private Collection $sentMessages;
    
    // Reports sent by this user: owning side in Report should use inversedBy="sentReports"
    #[ORM\OneToMany(mappedBy: "userSender", targetEntity: Report::class)]
    private Collection $sentReports;
    
    // Reports received by this user: owning side in Report should use inversedBy="receivedReports"
    #[ORM\OneToMany(mappedBy: "userTarget", targetEntity: Report::class)]
    private Collection $receivedReports;
    
    // Test results: owning side in Test_result must use inversedBy="testResults"
    #[ORM\OneToMany(mappedBy: "user", targetEntity: Test_result::class)]
    private Collection $testResults;
    
    // Profile: owning side in Profile should use inversedBy="profile" (or adjust consistently)
    #[ORM\OneToOne(mappedBy: "user", targetEntity: Profile::class)]
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
    
    // Getters and setters
    
    public function getId_user(): int
    {
        return $this->id_user;
    }
    
    public function setId_user(int $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    
    public function getRole(): string
    {
        return $this->role;
    }
    
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }
    
    public function getImage(): string
    {
        return $this->image;
    }
    
    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }
    
    public function getChannelsInitiated(): Collection
    {
        return $this->channelsInitiated;
    }
    
    public function getChannelsReceived(): Collection
    {
        return $this->channelsReceived;
    }
    
    public function getServiceApplications(): Collection
    {
        return $this->serviceApplications;
    }
    
    public function getJobApplications(): Collection
    {
        return $this->jobApplications;
    }
    
    public function getJobOffers(): Collection
    {
        return $this->jobOffers;
    }
    
    public function getServiceOffers(): Collection
    {
        return $this->serviceOffers;
    }
    
    public function getCvs(): Collection
    {
        return $this->cvs;
    }
    
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }
    
    public function getSentReports(): Collection
    {
        return $this->sentReports;
    }
    
    public function getReceivedReports(): Collection
    {
        return $this->receivedReports;
    }
    
    public function getTestResults(): Collection
    {
        return $this->testResults;
    }
    
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }
    
    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;
        return $this;
    }
}
