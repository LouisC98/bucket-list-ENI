<?php

namespace App\Controller;

use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comment', name: 'app_comment')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class CommentController extends AbstractController
{
    #[Route('/{id}/edit', name: '_edit', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $comment = $commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        $user = $this->getUser();
        if ($comment->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $form = $this->createForm(CommentType::class, $comment);

        if ($request->isMethod('GET')) {
            $html = $this->renderView('comment/_form.html.twig', [
                'form' => $form,
                'comment' => $comment
            ]);
            return new JsonResponse(['html' => $html]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $html = $this->renderView('comment/_card.html.twig', [
                'comment' => $comment
            ]);
            return new JsonResponse(['success' => true, 'html' => $html]);
        }

        $html = $this->renderView('comment/_form.html.twig', [
            'form' => $form,
            'comment' => $comment
        ]);
        return new JsonResponse(['success' => false, 'html' => $html], 400);
    }

    #[Route('/{id}', name: '_delete', requirements: ['id'=>'\d+'], methods: ['DELETE'])]
    public function delete(int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $comment = $commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        $user = $this->getUser();
        if ($comment->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
