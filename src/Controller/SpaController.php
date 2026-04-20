<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SpaController extends AbstractController
{
    // entrada principal de la app
    #[Route('/', name: 'spa')]
    public function index(): Response
    {
        return $this->render('app.html.twig');
    }

    // fallback para Vue Router
    #[Route('/{any}', name: 'spa_fallback', requirements: ['any' => '^(?!api).*$'])]
    public function fallback(): Response
    {
        return $this->render('app.html.twig');
    }
}
