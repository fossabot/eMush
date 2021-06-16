<?php

namespace Mush\Game\Service;

interface TranslationServiceInterface
{
    public function translate(string $key, array $parameters, string $domain): string;
}
