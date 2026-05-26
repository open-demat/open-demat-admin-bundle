<?php

namespace OpenDemat\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AdminBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}