<?php

namespace App\Service;

use App\Repository\WishRepository;
use Symfony\Component\HttpFoundation\Request;

class WishSearchService
{
    public function __construct(
        private WishRepository $wishRepository
    ) {}

    public function searchFromRequest(Request $request, bool $showOnlyPublished = true, ?int $userId = null): array
    {
        $criteria = $this->parseSearchCriteria($request, $userId);

        return $this->wishRepository->findByCriteria(
            $criteria['search'],
            $criteria['orderBy'],
            $criteria['isCompleted'],
            $showOnlyPublished,
            $userId
        );
    }

    private function parseSearchCriteria(Request $request, int $userId = null): array
    {
        $searchInput = $request->query->getString('search');

        $sort = $request->query->getString('sort');
        $orderBy = [];
        if ($sort) {
            $order = $sort === 'oldest' ? 'ASC' : 'DESC';
            $orderBy = ['createdAt' => $order];
        } elseif ($userId !== null) {
            $orderBy = ['createdAt' => 'DESC'];
        }

        $statusParam = $request->query->get('status');
        $isCompleted = null;
        if ($statusParam === '1' || $statusParam === 'true') {
            $isCompleted = true;
        } elseif ($statusParam === '0' || $statusParam === 'false') {
            $isCompleted = false;
        }

        return [
            'search' => $searchInput,
            'orderBy' => $orderBy,
            'isCompleted' => $isCompleted
        ];
    }
}