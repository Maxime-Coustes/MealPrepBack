<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PingController extends AbstractController
{
    #[Route('/ping', name: 'app_ping')]
    public function index(): Response
    {
        return $this->render('ping/index.html.twig', [
            'controller_name' => 'PingController',
        ]);
    }
    // Both works
    // public function ping()
    // {
    //     return new JsonResponse(['status' => 'OK']);
    // }
}
