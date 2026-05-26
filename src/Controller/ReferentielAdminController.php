<?php

namespace OpenDemat\AdminBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenDemat\Core\Entity\Referentiel\AbstractReferentiel;
use OpenDemat\Core\Referentiel\ReferentielFormFactory;
use OpenDemat\Core\Referentiel\ReferentielRegistry;

#[Route('/admin/referentiels')]
#[IsGranted('ROLE_ADMIN')]
final class ReferentielAdminController extends AbstractController
{
    #[Route('/', name: 'open_demat_admin_referentiels_index', methods: ['GET'])]
    public function index(ReferentielRegistry $registry, Request $request): Response
    {
        $q = trim((string) $request->query->get('q', ''));

        $refs = $registry->all();

        if ($q !== '') {
            $qq = mb_strtolower($q);
            $refs = array_values(array_filter($refs, static function ($def) use ($qq) {
                $hay = mb_strtolower(($def->schema ?? '') . '.' . $def->table . ' ' . $def->shortName . ' ' . $def->entityClass);
                return str_contains($hay, $qq);
            }));
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/referentiel/index.html.twig', [
            'q' => $q,
            'refs' => $refs,
        ]);
    }

    #[Route('/{key}', name: 'open_demat_admin_referentiels_list', methods: ['GET'])]
    public function list(
        string $key,
        ReferentielRegistry $registry,
        ManagerRegistry $doctrine,
        Request $request,
    ): Response {
        $key = urldecode($key);

        $q = trim((string) $request->query->get('q', ''));
        $onlyActif = (string) $request->query->get('actif', '1') !== '0';

        $def = $registry->get($key);
        $em  = $doctrine->getManagerForClass($def->entityClass);

        $repo = $em->getRepository($def->entityClass);

        // Simple: on récupère et on filtre côté PHP si recherche.
        // (si besoin perf, on fera un QueryBuilder générique)
        $items = $repo->findBy([], ['libelle' => 'ASC']);

        if ($onlyActif) {
            $items = array_values(array_filter($items, static fn ($e) => method_exists($e, 'isActif') ? $e->isActif() : true));
        }

        if ($q !== '') {
            $qq = mb_strtolower($q);
            $items = array_values(array_filter($items, static function ($e) use ($qq) {
                /** @var AbstractReferentiel $e */
                $hay = mb_strtolower($e->getCode() . ' ' . $e->getLibelle());
                return str_contains($hay, $qq);
            }));
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/referentiel/list.html.twig', [
            'def' => $def,
            'key' => $key,
            'q' => $q,
            'onlyActif' => $onlyActif,
            'items' => $items,
        ]);
    }

    #[Route('/{key}/new', name: 'open_demat_admin_referentiels_new', methods: ['GET', 'POST'])]
    public function new(
        string $key,
        ReferentielRegistry $registry,
        ManagerRegistry $doctrine,
        ReferentielFormFactory $forms,
        Request $request,
    ): Response {
        $key = urldecode($key);

        $def = $registry->get($key);
        $em  = $doctrine->getManagerForClass($def->entityClass);

        // Idéalement: rendre le ctor optionnel dans AbstractReferentiel.
        $entity = $this->newEmptyEntity($def->entityClass);

        $form = $forms->create($def, $entity)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', 'Référentiel créé.');
            return $this->redirectToRoute('open_demat_admin_referentiels_list', ['key' => urlencode($key)]);
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/referentiel/form.html.twig', [
            'def' => $def,
            'key' => $key,
            'form' => $form,
            'mode' => 'new',
        ]);
    }

    #[Route('/{key}/{id}/edit', name: 'open_demat_admin_referentiels_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        string $key,
        int $id,
        ReferentielRegistry $registry,
        ManagerRegistry $doctrine,
        ReferentielFormFactory $forms,
        Request $request,
    ): Response {
        $key = urldecode($key);

        $def = $registry->get($key);
        $em  = $doctrine->getManagerForClass($def->entityClass);

        $entity = $em->find($def->entityClass, $id);
        if (!$entity instanceof AbstractReferentiel) {
            throw $this->createNotFoundException();
        }

        $form = $forms->create($def, $entity)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity->touch();
            $em->flush();

            $this->addFlash('success', 'Référentiel mis à jour.');
            return $this->redirectToRoute('open_demat_admin_referentiels_list', ['key' => urlencode($key)]);
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/referentiel/form.html.twig', [
            'def' => $def,
            'key' => $key,
            'form' => $form,
            'mode' => 'edit',
            'entity' => $entity,
        ]);
    }

    #[Route('/{key}/{id}/delete', name: 'open_demat_admin_referentiels_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        string $key,
        int $id,
        ReferentielRegistry $registry,
        ManagerRegistry $doctrine,
        Request $request,
    ): Response {
        $key = urldecode($key);

        if (!$this->isCsrfTokenValid('delete_ref_' . $key . '_' . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('open_demat_admin_referentiels_list', ['key' => urlencode($key)]);
        }

        $def = $registry->get($key);
        $em  = $doctrine->getManagerForClass($def->entityClass);

        $entity = $em->find($def->entityClass, $id);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $em->remove($entity);
        $em->flush();

        $this->addFlash('success', 'Référentiel supprimé.');
        return $this->redirectToRoute('open_demat_admin_referentiels_list', ['key' => urlencode($key)]);
    }

    private function newEmptyEntity(string $class): object
    {
        $rc = new \ReflectionClass($class);
        return $rc->newInstanceWithoutConstructor();
    }
}
