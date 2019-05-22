<?php
namespace Deozza\PhilarmonyUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="Deozza\PhilarmonyUserBundle\Repository\UserRepository")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"user_id", "entity_complete"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @JMS\Groups({"user_basic", "username", "entity_complete"})
     */
    private $username;

    /**
     * @Assert\Type("string")
     * @JMS\Exclude
     */
    private $plainPassword;

    /**
     * @Assert\Type("string")
     * @JMS\Exclude
     */
    private $newPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Exclude
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @JMS\Groups({"user_basic"})
     */
    private $email;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({"user_advanced"})
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({"user_advanced"})
     */
    private $lastFailedLogin;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"user_advanced"})
     */
    private $registerDate;

    /**
     * @ORM\Column(type="boolean")
     * @JMS\Groups({"user_advanced"})
     */
    private $active;


    /**
     * @ORM\Column(type="json")
     * @JMS\Groups({"user_advanced"})
     */
    private $roles = [];


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->active = false;
        $this->registerDate = new \DateTime('now');
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLastFailedLogin(): ?\DateTimeInterface
    {
        return $this->lastFailedLogin;
    }

    public function setLastFailedLogin(?\DateTimeInterface $lastFailedLogin): self
    {
        $this->lastFailedLogin = $lastFailedLogin;

        return $this;
    }

    public function getRegisterDate(): ?\DateTimeInterface
    {
        return $this->registerDate;
    }

    public function setRegisterDate(\DateTimeInterface $registerDate): self
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getRoles() :array
    {
        return array_unique(array_merge(['ROLE_USER'], $this->roles));
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }


    public function getSalt()
    {
    }
    public function eraseCredentials()
    {
    }
}