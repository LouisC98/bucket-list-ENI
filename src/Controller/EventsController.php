<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/events', name: 'app_events')]
final class EventsController extends AbstractController
{
    #[Route('/', name: '_index', methods: ['GET'])]
    public function index(SerializerInterface $serializer, Request $request): Response
    {
        $city = $request->query->getString('city');
        $startDateString = $request->query->getString('startDate');

        $page = $request->query->getInt('page', 1);
        $rows = $request->query->getInt('rows', 10);
        $start = ($page - 1) * $rows;

        $apiUrl = sprintf(
            'https://public.opendatasoft.com/api/records/1.0/search/?dataset=evenements-publics-openagenda&rows=%d&start=%d',
            $rows,
            $start
        );

        $filters = [];

        if (!empty($city)) {
            $filters[] = 'refine.location_city=' . urlencode($city);
        }

        if (!empty($startDateString)) {
            $filters[] = 'refine.firstdate_begin=' . urlencode($startDateString);
        }

        if (!empty($filters)) {
            $apiUrl .= '&' . implode('&', $filters);
        }

        $response = file_get_contents($apiUrl);

        $content = $serializer->decode($response, 'json');

        $events = $content['records'];
        $totalCount = $content['nhits'];

        $eventsClean = array_map(function ($event) {
            return [
                "recordid" => $event['recordid'],
                "daterange_fr" => $event['fields']['daterange_fr'],
                "thumbnail" => $event['fields']['thumbnail'] ?? null,
                "title_fr" => $event['fields']['title_fr'],
                "location_name" => $event['fields']['location_name'],
                "location_address" => $event['fields']['location_address'],
                "description_fr" => $event['fields']['description_fr'],
                "canonicalurl" => $event['fields']['canonicalurl'],
            ];
        }, $events);

        $totalPages = ceil($totalCount / $rows);

        return $this->render('events/index.html.twig', [
            "events" => $eventsClean,
            "pagination" => [
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "totalItems" => $totalCount,
                "itemsPerPage" => $rows,
                "hasNext" => $page < $totalPages,
                "hasPrev" => $page > 1
            ],
            "currentFilters" => [
                "city" => $city,
                "startDate" => $startDateString
            ]
        ]);
    }
}
