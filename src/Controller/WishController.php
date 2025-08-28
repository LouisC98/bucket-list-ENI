<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\UserRepository;
use App\Repository\WishRepository;
use App\Service\WishSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wish')]
final class WishController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_wish_user', requirements: ['id'=>'\d+'], methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function userWishes(int $id, Request $request, WishSearchService $searchService, UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'Veuillez vous connecter');
            return $this->redirectToRoute('app_login');
        }

        $targetUser = $userRepository->find($id);
        if (!$targetUser) {
            $this->addFlash('error', 'Utilisateur non trouvé');
            return $this->redirectToRoute('app_home');
        }


        $isOwner = $currentUser->getId() === $targetUser->getId();
        $showOnlyPublished = !$isOwner;

        $wishes = $searchService->searchFromRequest($request, $showOnlyPublished, $targetUser->getId());

        if ($request->isXmlHttpRequest()) {
            return $this->render('partials/_wishes_list.html.twig', [
                'wishes' => $wishes,
            ]);
        }

        return $this->render('wish/index.html.twig', [
            'wishes' => $wishes,
            'isOwner' => $isOwner,
            'user' => $targetUser
        ]);
    }

    #[Route('/new', name: 'app_wish_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $wish = new Wish();
        $user = $this->getUser();
        $form = $this->createForm(WishType::class, $wish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wish->setAuthor($user);
            $entityManager->persist($wish);
            $entityManager->flush();
            $this->addFlash('success', 'Wish crée ! ✅');

            return $this->redirectToRoute('app_wish_show', ['id' => $wish->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wish/new.html.twig', [
            'wish' => $wish,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wish_show', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(int $id, WishRepository $wishRepository): Response
    {
        $wish = $wishRepository->find($id);
        if (!$wish) {
            $this->addFlash('error', 'Wish non trouvé ❌');
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('wish/show.html.twig', [
            'wish' => $wish,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/edit', name: 'app_wish_edit', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, WishRepository $wishRepository, EntityManagerInterface $entityManager): Response
    {
        $wish = $wishRepository->find($id);
        if (!$wish) {
            $this->addFlash('error', 'Wish non trouvé ❌');
            return $this->redirectToRoute('app_wish_index', [], Response::HTTP_SEE_OTHER);
        }

        $user = $this->getUser();
        if ($wish->getAuthor() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas le propriétaire');
            return $this->redirectToRoute('app_wish_user', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        $isPublishedBefore = $wish->isPublished();

        $form = $this->createForm(WishType::class, $wish);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $isPublishedAfter = $wish->isPublished();

            if ($isPublishedBefore !== $isPublishedAfter) {
                if ($isPublishedAfter) {
                    $this->addFlash('success', 'Wish publié ! ✅');
                } else {
                    $this->addFlash('error', 'Wish privé ! 📝');
                }
            } else {
                $this->addFlash('success', 'Wish modifié ! ✅');
            }

            return $this->redirectToRoute('app_wish_show', ['id' => $wish->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wish/edit.html.twig', [
            'wish' => $wish,
            'form' => $form,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/status', name: 'app_wish_status', requirements: ['id'=>'\d+'], methods: ['PATCH'])]
    public function changeWishStatus(int $id, WishRepository $wishRepository, EntityManagerInterface $entityManager): Response
    {
        try {
            $wish = $wishRepository->find($id);
            if (!$wish) {
                return new JsonResponse(['error' => true, 'message' => 'Wish non trouvé ❌']);
            }

            $user = $this->getUser();
            if ($wish->getAuthor() !== $user) {
                return new JsonResponse(['error' => true, 'message' => 'Vous n\'êtes pas le propriétaire ❌']);
            }

            $wish->setIsCompleted(!$wish->isCompleted());
            $entityManager->flush();
            return new JsonResponse(['success' => true, 'isCompleted' => $wish->isCompleted()]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => true, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}', name: 'app_wish_delete', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function delete(Request $request, Wish $wish, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wish->getId(), $request->get('_token'))) {
            $entityManager->remove($wish);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
