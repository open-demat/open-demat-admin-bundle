<?php

namespace OpenDemat\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'open_demat_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('@OpenDemat/admin-bundle/src/templates/dashboard.html.twig');
    }
}
