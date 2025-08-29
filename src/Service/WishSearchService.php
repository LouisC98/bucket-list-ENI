<?php

namespace App\Service;

use App\Repository\WishRepository;
use Symfony\Component\HttpFoundation\Request;

class WishSearchService
{
    public function __construct(
        private WishRepository $wishRepository
    ) {}

    public function searchFromRequestPaginated(Request $request, bool $showOnlyPublished = true, ?int $userId = null, int $offset = 0)
    {
        $criteria = $this->parseSearchCriteria($request, $userId);

        return $this->wishRepository->getWishPaginator(
            $criteria['search'],
            $criteria['orderBy'],
            $criteria['isCompleted'],
            $showOnlyPublished,
            $userId,
            $offset
        );
    }
    private function parseSearchCriteria(Request $request, int $userId = null): array
    {
        $searchInput = $request->query->getString('search');

        $sort = $request->query->getString('sort');
        $orderBy = [];
        if ($sort && in_array($sort, ['ASC', 'DESC'])) {
            $orderBy = ['createdAt' => $sort];
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