<?php
namespace PhilarmonyUserBundle\Entity;

use PhilarmonyUserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;


class ApiTokenKind
{
    const AUTH = "AUTH_TOKEN";
    const RESET = "RESET_TOKEN";
    const MAGIC = "MAGIC_TOKEN";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $kind;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKind(): ?string
    {
        return $this->kind;
    }

    public function setKind(string $kind): self
    {
        $this->kind = $kind;
        return $this;
    }
}