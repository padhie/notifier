<?php

namespace App\Entity;

use App\Repository\InboundRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InboundRepository::class)
 */
class Inbound
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $hash;

    /**
     * @var Collection<Outbound>
     * @ORM\OneToMany(targetEntity="Outbound", mappedBy="inbound")
     */
    private Collection $outbounds;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTimeInterface $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTimeInterface $updated_at;

    public function __construct()
    {
        $this->outbounds = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return Collection<Outbound>
     */
    public function getOutbounds(): Collection
    {
        return $this->outbounds;
    }

    public function addOutbound(Outbound $outbound): self
    {
        $this->outbounds->add($outbound);

        return $this;
    }

    /**
     * @param Collection<Outbound> $outbounds
     */
    public function setOutbounds(Collection $outbounds): self
    {
        $this->outbounds = $outbounds;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
