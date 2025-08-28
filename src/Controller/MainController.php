<?php

namespace App\Controller;

use App\Repository\WishRepository;
use App\Service\WishSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(Request $request, WishSearchService $searchService): Response
    {
        $wishes = $searchService->searchFromRequest($request);

        if ($request->isXmlHttpRequest()) {
            return $this->render('partials/_wishes_list.html.twig', [
                'wishes' => $wishes,
            ]);
        }

        return $this->render('main/index.html.twig', [
            'wishes' => $wishes,
        ]);
    }

    #[Route('/about-us', name: 'app_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('main/about_us.html.twig');
    }
}
