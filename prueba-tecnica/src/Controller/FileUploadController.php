<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FileUploadController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/upload', name: 'upload_file', methods: ['POST'])]
    public function uploadFile(Request $request): JsonResponse
    {
        $file = $request->files->get('excel_file');

        if (!$file) {
            return new JsonResponse(['error' => 'No se ha subido el archivo'], Response::HTTP_BAD_REQUEST);
        }

        // Validar extensión del archivo
        $allowedExtensions = ['xls', 'xlsx'];
        $extension = $file->guessExtension();
        if (!in_array($extension, $allowedExtensions)) {
            return new JsonResponse(['error' => 'Tipo de archivo inválido. Solo .xls y .xlsx es aceptado.'], Response::HTTP_BAD_REQUEST);
        }

        // Directorio de almacenamiento
        $uploadsDir = $this->getParameter('upload_directory');
        $newFilename = 'PruebaTecnica.xlsx';

        try {
            $file->move($uploadsDir, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Error al subir el archivo: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Llamar a Litestar para procesar el archivo
        try {
            $response = $this->httpClient->request('GET', 'http://127.0.0.1:8001/procesar');

            $data = $response->toArray();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al procesar el archivo en Litestar: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'Archivo subido y procesado correctamente', 'litestar_response' => $data]);
    }
}
