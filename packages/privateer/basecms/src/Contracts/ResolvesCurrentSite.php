<?php

namespace Privateer\Basecms\Contracts;

use Privateer\Basecms\Models\Site;

interface ResolvesCurrentSite
{
    public function resolve(): ?Site;
}
