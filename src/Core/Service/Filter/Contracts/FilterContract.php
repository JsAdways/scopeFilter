<?php

namespace Jsadways\ScopeFilter\Core\Service\Filter\Contracts;

use Jsadways\ScopeFilter\Services\Filter\FilterFormatDto;
use Jsadways\ScopeFilter\Services\Filter\FilterGetRelationModelDto;

interface FilterContract
{
    public function format(FilterFormatDto $filters): array;
    public function getRelationModel(FilterGetRelationModelDto $data):mixed;
}
