<?php

namespace App\Controller\AdminController;

use App\Entity\Wish;
use App\Repository\CategoryRepository;
use App\Repository\WishRepository;
use App\Service\PaginationService;
use App\Service\WishSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wish/admin', name: 'app_wish_admin')]
#[isGranted('ROLE_ADMIN')]
final class WishController extends AbstractController
{
    #[Route('/', name: '_index', methods: ['GET'])]
    public function index(Request $request, WishSearchService $searchService, PaginationService $paginationService, CategoryRepository $categoryRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset'));
        $paginator = $searchService->searchFromRequestPaginated($request, false, null, $offset);

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

        return $this->render('wish/admin_index.html.twig', [
            'wishes' => $paginator,
            'pagination' => $paginationData,
            'categories' => $categoryRepository->findAll(),
        ]);
    }
    #[Route('/{id}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Wish $wish, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wish->getId(), $request->request->get('_token'))) {
            $entityManager->remove($wish);
            $entityManager->flush();
            $this->addFlash("error", "Le wish a été supprimé !");
        }

        return $this->redirectToRoute('app_wish_admin_index', [], Response::HTTP_SEE_OTHER);
    }

}
