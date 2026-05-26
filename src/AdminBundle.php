<?php

namespace OpenDemat\AdminBundle;

use OpenDemat\AdminBundle\DependencyInjection\AdminExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AdminBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new AdminExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
