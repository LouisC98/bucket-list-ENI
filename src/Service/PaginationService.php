<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationService
{
    public function createPaginationData(Paginator $paginator, Request $request, int $itemsPerPage = 6): array
    {
        $offset = max(0, $request->query->getInt('offset'));
        $total = count($paginator);

        return [
            'total' => $total,
            'hasNext' => ($offset + $itemsPerPage) < $total,
            'hasPrevious' => $offset > 0,
            'previousOffset' => max(0, $offset - $itemsPerPage),
            'nextOffset' => $offset + $itemsPerPage,
            'currentParams' => $request->query->all(),
            'currentPage' => intval($offset / $itemsPerPage) + 1,
            'totalPages' => $total > 0 ? ceil($total / $itemsPerPage) : 0
        ];
    }
}