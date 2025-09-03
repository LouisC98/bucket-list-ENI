<?php

namespace App\Twig;

use App\Service\Censurator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CensuratorExtension extends AbstractExtension
{
    public function __construct(private Censurator $censurator)
    {
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('purify', [$this->censurator, 'purify']),
        ];
    }
}