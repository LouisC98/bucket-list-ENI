<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Wish;
use App\Form\CommentType;
use App\Form\WishType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\WishRepository;
use App\Service\PaginationService;
use App\Service\WishSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wish')]
final class WishController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_wish_user', requirements: ['id'=>'\d+'], methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function userWishes(int $id, Request $request, WishSearchService $searchService, UserRepository $userRepository, PaginationService $paginationService, CategoryRepository $categoryRepository): Response
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

        $offset = max(0, $request->query->getInt('offset'));
        $paginator = $searchService->searchFromRequestPaginated($request, $showOnlyPublished, $targetUser->getId(), $offset);

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

        return $this->render('wish/index.html.twig', [
            'wishes' => $paginator,
            'isOwner' => $isOwner,
            'user' => $targetUser,
            'pagination' => $paginationData,
            'categories' => $categoryRepository->findAll(),
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

    #[Route('/{id}', name: 'app_wish_show', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function show(int $id, WishRepository $wishRepository, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $wish = $wishRepository->find($id);
        if (!$wish) {
            $this->addFlash('error', 'Wish non trouvé ❌');
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        $comments = $commentRepository->findBy(['wish' => $wish], ['createdAt' => 'DESC']);

        $user = $this->getUser();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setWish($wish);
            $comment->setUser($user);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_wish_show', ['id' => $wish->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wish/show.html.twig', [
            'wish' => $wish,
            'form' => $form->createView(),
            'comments' => $comments,
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
    public function delete(int $id, WishRepository $wishRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $wish = $wishRepository->find($id);
        if (!$wish) {
            $this->addFlash("error", "Wish non trouvé ❌");
            return $this->redirectToRoute('app_wish_user', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        $user = $this->getUser();
        if ($wish->getAuthor() !== $user) {
            $this->addFlash("error", "Vous n\'êtes pas le propriétaire ❌");
            return $this->redirectToRoute('app_wish_user', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('delete'.$wish->getId(), $request->get('_token'))) {
            $entityManager->remove($wish);
            $entityManager->flush();
            $this->addFlash("error", "Le wish a bien été supprimé");
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
