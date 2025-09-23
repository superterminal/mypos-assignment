<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReactController extends AbstractController
{
    #[Route('/', name: 'app_react_app')]
    #[Route('/app', name: 'app_react_app_legacy')]
    #[Route('/{path}', name: 'app_react_app_catchall', requirements: ['path' => '^(?!api|_profiler|_wdt).*'])]
    public function index(): Response
    {
        return $this->render('react_app.html.twig');
    }
}
