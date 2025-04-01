<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "rentas")]
class Renta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Cargo::class)]
    #[ORM\JoinColumn(name: "cargo_id", referencedColumnName: "id", nullable: false)]
    private Cargo $cargo;

    #[ORM\Column(type: "float")]
    private float $rentaBruta;

    public function getId(): int { return $this->id; }
    public function getCargo(): Cargo { return $this->cargo; }
    public function getRentaBruta(): float { return $this->rentaBruta; }
}
