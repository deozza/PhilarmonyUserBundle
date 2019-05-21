<?php
namespace Deozza\PhilarmonyUserBundle\User\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Registration
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $login;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $password;

    /**
     * @var boolean
     *
     * @Assert\NotBlank()
     * @Assert\Type("boolean")
     */
    private $acceptCGU;

    /**
     * @var boolean
     *
     * @Assert\Type("boolean")
     */
    private $acceptNewsletter;



    /**
     * @return string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAcceptCGU(): ?bool
    {
        return $this->acceptCGU;
    }

    /**
     * @param bool $acceptCGU
     */
    public function setAcceptCGU(bool $acceptCGU): self
    {
        $this->acceptCGU = $acceptCGU;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAcceptNewsletter(): ?bool
    {
        return $this->acceptNewsletter;
    }

    /**
     * @param bool $acceptNewsletter
     */
    public function setAcceptNewsletter(bool $acceptNewsletter): self
    {
        $this->acceptNewsletter = $acceptNewsletter;
        return $this;
    }

}