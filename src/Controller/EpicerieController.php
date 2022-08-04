<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EpicerieController extends AbstractController
{
    #[Route('/epicerie', name: 'app_epicerie')]
    public function index(): Response
    {
        return $this->render('epicerie/index.html.twig', [
            'controller_name' => 'EpicerieController',
        ]);
    }
}
