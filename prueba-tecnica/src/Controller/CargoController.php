<?php
namespace App\Controller;

use App\Entity\Cargo;
use App\Entity\Renta;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CargoController extends AbstractController
{
    #[Route('/cargos', name: 'get_cargos', methods: ['GET'])]
    public function getCargos(EntityManagerInterface $entityManager): JsonResponse
    {
        $cargos = $entityManager->getRepository(Cargo::class)->findAll();

        $data = array_map(function (Cargo $cargo) {
            return [
                'id' => $cargo->getId(),
                'nombre' => $cargo->getNombre()
            ];
        }, $cargos);

        return new JsonResponse($data);
    }

    #[Route('/renta/{id}', name: 'get_renta', methods: ['GET'])]
    public function getRenta(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $renta = $entityManager->getRepository(Renta::class)->findOneBy(['cargo' => $id]);

        if (!$renta) {
            return new JsonResponse(['error' => 'No se encontró la renta para el cargo seleccionado'], 404);
        }

        return new JsonResponse([
            'renta_bruta' => $renta->getRentaBruta()
        ]);
    }
}
