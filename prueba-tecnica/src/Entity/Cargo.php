<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "cargos")]
class Cargo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $nombre;

    #[ORM\Column(type: "string", length: 50)]
    private string $grado;

    #[ORM\Column(type: "string", length: 50)]
    private string $genero;

    #[ORM\Column(type: "string", length: 100)]
    private string $nacionalidad;

    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
}
