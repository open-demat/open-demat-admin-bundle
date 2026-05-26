<?php

namespace OpenDemat\AdminBundle\Twig;

use Twig\Extension\AbstractExtension;

final class AdminOrganizationExtension extends AbstractExtension
{
    public function __construct(
        private readonly ?string $organizationName,
        private readonly ?string $organizationLogo,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'open_demat_admin_organization_name' => $this->organizationName ?: 'Open Demat',
            'open_demat_admin_organization_logo' => $this->organizationLogo ?? '',
        ];
    }
}
