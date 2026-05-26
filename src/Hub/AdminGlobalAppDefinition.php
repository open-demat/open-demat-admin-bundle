<?php

namespace OpenDemat\AdminBundle\Hub;

use OpenDemat\Core\Hub\AppDefinitionInterface;

class AdminGlobalAppDefinition implements AppDefinitionInterface
{
    public function getKey(): string
    {
        return 'ADMIN';
    }

    public function getTitle(): string
    {
        return 'Administration globale';
    }

    public function getRoute(): string
    {
        return 'open_demat_admin_dashboard';
    }

    public function getIcon(): string
    {
        return 'system.svg';
    }

    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }

    public function getDescription(): string
    {
        return 'Gérer les comptes, les droits et les accès aux différentes applications.';
    }
}
