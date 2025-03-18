<?php

namespace Router\Filter;

use Router\Request\Request;
use Router\Response\Response;

interface Filter
{
    public function filter(Request $request, Response $response): bool;
}
