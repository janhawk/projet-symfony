<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VinsController extends AbstractController
{
    #[Route('/vins', name: 'app_vins')]
    public function index(): Response
    {
        return $this->render('vins/index.html.twig', [
            'controller_name' => 'VinsController',
        ]);
    }
}
