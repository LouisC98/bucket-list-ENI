<?php

namespace App\Controller;

use App\Repository\WishRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(WishRepository $wishRepository): Response
    {
        return $this->render('main/index.html.twig', [
            'wishes' => $wishRepository->findAll(),
        ]);
    }

    #[Route('/about-us', name: 'app_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('main/about_us.html.twig');
    }
}
