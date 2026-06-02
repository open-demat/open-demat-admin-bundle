<?php

namespace OpenDemat\AdminBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class AdminOrganizationExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly ?string $organizationName,
        private readonly ?string $organizationLogo,
        private readonly ?string $themePrimaryColor,
        private readonly ?string $themePrimaryDarkColor,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'open_demat_admin_organization_name' => $this->organizationName ?: 'Open Demat',
            'open_demat_admin_organization_logo' => $this->organizationLogo ?: 'assets/img/open-demat-logo.png',
            'open_demat_admin_theme_primary_color' => $this->themePrimaryColor ?: '#E30613',
            'open_demat_admin_theme_primary_dark_color' => $this->themePrimaryDarkColor ?: '#15202B',
        ];
    }
}
