<?php

namespace App\Service\Extractor;

use App\Entity\PageLinkEntityInterface;

interface ExtractorInterface
{
    public function extract(PageLinkEntityInterface $entity): array;
}