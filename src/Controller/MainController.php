<?php

namespace App\Controller;

use App\Repository\WishRepository;
use App\Service\PaginationService;
use App\Service\WishSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(Request $request, WishSearchService $searchService, PaginationService $paginationService): Response
    {
        $offset = max(0, $request->query->getInt('offset'));
        $paginator = $searchService->searchFromRequestPaginated($request, true, null, $offset);

        $paginationData = $paginationService->createPaginationData(
            $paginator,
            $request,
            WishRepository::WISHES_PER_PAGE
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('partials/_wishes_list.html.twig', [
                'wishes' => $paginator,
                'pagination' => $paginationData
            ]);
        }

        return $this->render('main/index.html.twig', [
            'wishes' => $paginator,
            'pagination' => $paginationData
        ]);
    }

    #[Route('/about-us', name: 'app_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('main/about_us.html.twig');
    }
}
