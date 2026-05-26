<?php

namespace OpenDemat\AdminBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenDemat\Core\Process\AbstractProcessEntity;
use OpenDemat\Core\Process\ProcessFormFactory;
use OpenDemat\Core\Process\ProcessRegistry;

#[Route('/admin/process')]
#[IsGranted('ROLE_ADMIN')]
final class ProcessAdminController extends AbstractController
{
    #[Route('/', name: 'open_demat_admin_process_index', methods: ['GET'])]
    public function index(ProcessRegistry $registry, Request $request): Response
    {
        $q = trim((string) $request->query->get('q', ''));

        $defs = $registry->all();

        if ($q !== '') {
            $qq = mb_strtolower($q);
            $defs = array_values(array_filter($defs, static function ($def) use ($qq) {
                $hay = mb_strtolower(($def->schema ?? '') . '.' . $def->table . ' ' . $def->shortName . ' ' . $def->entityClass . ' ' . ($def->key ?? ''));
                return str_contains($hay, $qq);
            }));
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/process/index.html.twig', [
            'q' => $q,
            'defs' => $defs,
        ]);
    }

    #[Route('/{key}', name: 'open_demat_admin_process_list', methods: ['GET'])]
    public function list(
        string $key,
        ProcessRegistry $registry,
        ManagerRegistry $doctrine,
        Request $request,
    ): Response {
        $key = urldecode($key);

        $q = trim((string) $request->query->get('q', ''));
        $statut = trim((string) $request->query->get('statut', ''));

        $def = $registry->get($key);
        $em  = $doctrine->getManagerForClass($def->entityClass);
        $repo = $em->getRepository($def->entityClass);

        // Simple & safe : recherche/filtre en mémoire (comme tes référentiels)
        $items = $repo->findBy([], ['id' => 'DESC']);

        if ($statut !== '') {
            $items = array_values(array_filter($items, static function ($e) use ($statut) {
                /** @var AbstractProcessEntity $e */
                return method_exists($e, 'getStatut') ? $e->getStatut() === $statut : true;
            }));
        }

        if ($q !== '') {
            $qq = mb_strtolower($q);
            $items = array_values(array_filter($items, static function ($e) use ($qq) {
                /** @var AbstractProcessEntity $e */
                $id = method_exists($e, 'getId') ? (string) $e->getId() : '';
                $st = method_exists($e, 'getStatut') ? (string) $e->getStatut() : '';
                $hay = mb_strtolower($id . ' ' . $st);
                return str_contains($hay, $qq);
            }));
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/process/list.html.twig', [
            'def' => $def,
            'key' => $key,
            'q' => $q,
            'statut' => $statut,
            'items' => $items,
        ]);
    }

    #[Route('/{key}/new', name: 'open_demat_admin_process_new', methods: ['GET', 'POST'])]
    public function new(
        string $key,
        ProcessRegistry $registry,
        ManagerRegistry $doctrine,
        ProcessFormFactory $forms,
        Request $request,
    ): Response {
        $key = urldecode($key);

        $def = $registry->get($key);
        $em  = $doctrine->getManagerForClass($def->entityClass);

        $entity = $this->newEmptyEntity($def->entityClass);

        $form = $forms->create($def, $entity)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', 'Dossier créé.');
            return $this->redirectToRoute('open_demat_admin_process_list', ['key' => urlencode($key)]);
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/process/form.html.twig', [
            'def' => $def,
            'key' => $key,
            'form' => $form,
            'mode' => 'new',
        ]);
    }

    #[Route('/{key}/{id}/edit', name: 'open_demat_admin_process_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        string $key,
        int $id,
        ProcessRegistry $registry,
        ManagerRegistry $doctrine,
        ProcessFormFactory $forms,
        Request $request,
    ): Response {
        $key = urldecode($key);

        $def = $registry->get($key);
        $em  = $doctrine->getManagerForClass($def->entityClass);

        $entity = $em->find($def->entityClass, $id);
        if (!$entity instanceof AbstractProcessEntity) {
            throw $this->createNotFoundException();
        }

        $form = $forms->create($def, $entity)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity->touch();
            $em->flush();

            $this->addFlash('success', 'Dossier mis à jour.');
            return $this->redirectToRoute('open_demat_admin_process_list', ['key' => urlencode($key)]);
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/process/form.html.twig', [
            'def' => $def,
            'key' => $key,
            'form' => $form,
            'mode' => 'edit',
            'entity' => $entity,
        ]);
    }

    #[Route('/{key}/{id}/delete', name: 'open_demat_admin_process_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        string $key,
        int $id,
        ProcessRegistry $registry,
        ManagerRegistry $doctrine,
        Request $request,
    ): Response {
        $key = urldecode($key);

        if (!$this->isCsrfTokenValid('delete_process_' . $key . '_' . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('open_demat_admin_process_list', ['key' => urlencode($key)]);
        }

        $def = $registry->get($key);
        $em  = $doctrine->getManagerForClass($def->entityClass);

        $entity = $em->find($def->entityClass, $id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $em->remove($entity);
        $em->flush();

        $this->addFlash('success', 'Dossier supprimé.');
        return $this->redirectToRoute('open_demat_admin_process_list', ['key' => urlencode($key)]);
    }

    private function newEmptyEntity(string $class): object
    {
        $rc = new \ReflectionClass($class);
        return $rc->newInstanceWithoutConstructor();
    }
}
