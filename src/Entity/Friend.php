<?php

namespace App\Entity;

use App\Repository\FriendRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FriendRepository::class)
 */
class Friend
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="friends")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userSource;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $userTarget;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserSource(): ?User
    {
        return $this->userSource;
    }

    public function setUserSource(?User $userSource): self
    {
        $this->userSource = $userSource;

        return $this;
    }

    public function getUserTarget(): ?User
    {
        return $this->userTarget;
    }

    public function setUserTarget(?User $userTarget): self
    {
        $this->userTarget = $userTarget;

        return $this;
    }
}
