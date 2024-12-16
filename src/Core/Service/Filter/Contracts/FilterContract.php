<?php

namespace Jsadways\ScopeFilter\Core\Service\Filter\Contracts;

use Jsadways\ScopeFilter\Services\Filter\FilterFormatDto;
use Jsadways\ScopeFilter\Services\Filter\FilterGetTableDto;

interface FilterContract
{
    public function format(FilterFormatDto $filters): array;
    public function getTable(FilterGetTableDto $data): string;
}
