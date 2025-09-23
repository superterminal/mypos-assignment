<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReactController extends AbstractController
{
    #[Route('/', name: 'app_react_app')]
    #[Route('/app', name: 'app_react_app_legacy')]
    #[Route('/login', name: 'app_react_login')]
    #[Route('/register', name: 'app_react_register')]
    #[Route('/vehicles', name: 'app_react_vehicles')]
    #[Route('/vehicle/{id}', name: 'app_react_vehicle_show', requirements: ['id' => '\d+'])]
    #[Route('/merchant/vehicle/new', name: 'app_react_merchant_vehicle_new')]
    #[Route('/merchant/vehicle/{id}/edit', name: 'app_react_merchant_vehicle_edit', requirements: ['id' => '\d+'])]
    #[Route('/merchant/vehicles', name: 'app_react_merchant_vehicles')]
    #[Route('/buyer/followed', name: 'app_react_buyer_followed')]
    #[Route('/forgot-password', name: 'app_react_forgot_password')]
    #[Route('/reset-password/{token}', name: 'app_react_reset_password')]
    #[Route('/{path}', name: 'app_react_app_catchall', requirements: ['path' => '^(?!api|_profiler|_wdt|admin|bundles).*'])]
    public function index(): Response
    {
        return $this->render('react_app.html.twig');
    }
}
