<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//Renderizar la vista en templates.
class FrontendController extends AbstractController
{
    #[Route('/frontend', name: 'frontend')]
    public function index(): Response
    {
        return $this->render('frontend/index.html'); 
    }
}
