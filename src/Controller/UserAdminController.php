<?php

namespace OpenDemat\AdminBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenDemat\Core\Entity\User;
use OpenDemat\Core\Repository\RoleRepository;
use OpenDemat\Core\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users')]
final class UserAdminController extends AbstractController
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly ?RoleRepository $roleRepository = null,
    ) {
    }

    /**
     * @return string[]
     */
    private function getAvailableRoles(): array
    {
        if ($this->roleRepository !== null) {
            try {
                return $this->roleRepository->getFlattenedRoleHierarchy();
            } catch (\Throwable) {
                // Keep the admin usable before the role table migration has run.
            }
        }

        $roles = ['ROLE_USER', 'ROLE_ADMIN'];

        if ($this->parameterBag->has('security.role_hierarchy.roles')) {
            $hierarchy = $this->parameterBag->get('security.role_hierarchy.roles');

            if (is_array($hierarchy)) {
                foreach ($hierarchy as $role => $children) {
                    $roles[] = (string) $role;

                    if (is_array($children)) {
                        foreach ($children as $child) {
                            $roles[] = (string) $child;
                        }
                    }
                }
            }
        }

        $roles = array_values(array_unique(array_filter($roles)));
        sort($roles);

        return $roles;
    }

    #[Route('/', name: 'open_demat_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $q = trim((string) $request->query->get('q', ''));

        if ($q !== '') {
            $qb = $userRepository->createQueryBuilder('u');
            $qb->andWhere('u.username LIKE :q OR u.email LIKE :q')
                ->setParameter('q', '%' . $q . '%')
                ->orderBy('u.username', 'ASC');

            $users = $qb->getQuery()->getResult();
        } else {
            $users = $userRepository->findBy([], ['username' => 'ASC']);
        }

        return $this->render('@OpenDemat/admin-bundle/src/templates/user/index.html.twig', [
            'users' => $users,
            'availableRoles' => $this->getAvailableRoles(),
        ]);
    }

    #[Route('/create', name: 'open_demat_admin_user_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        if (!$this->isCsrfTokenValid('create_user', (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('open_demat_admin_user_index');
        }

        $username = trim((string) $request->request->get('username', ''));
        $email = trim((string) $request->request->get('email', ''));
        $roles = $request->request->all('roles') ?? [];

        if ($username === '') {
            $this->addFlash('danger', "Le nom d'utilisateur est obligatoire.");
            return $this->redirectToRoute('open_demat_admin_user_index');
        }

        $existing = $userRepository->findOneBy(['username' => $username]);
        if ($existing !== null) {
            $this->addFlash('danger', "Un utilisateur avec cet identifiant existe déjà.");
            return $this->redirectToRoute('open_demat_admin_user_index');
        }

        $allowedRoles = $this->getAvailableRoles();
        $roles = array_values(array_intersect($roles, $allowedRoles));

        if (empty($roles)) {
            $roles = ['ROLE_USER'];
        }

        $user = new User();
        $user->setUsername($username);

        if (method_exists($user, 'setEmail') && $email !== '') {
            $user->setEmail($email);
        }

        $user->setRoles($roles);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', sprintf('Utilisateur %s créé avec succès.', $user->getUsername()));

        return $this->redirectToRoute('open_demat_admin_user_index');
    }

    #[Route('/{id}/roles', name: 'open_demat_admin_user_roles_update', methods: ['POST'])]
    public function updateRoles(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('update_roles_' . $user->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('open_demat_admin_user_index');
        }

        $roles = $request->request->all('roles') ?? [];
        $allowedRoles = $this->getAvailableRoles();

        $roles = array_values(array_intersect($roles, $allowedRoles));

        if (empty($roles)) {
            $roles = ['ROLE_USER'];
        }

        $user->setRoles($roles);
        $user->bumpSessionVersion();

        $em->flush();

        $this->addFlash('success', 'Rôles mis à jour pour ' . $user->getUsername());

        return $this->redirectToRoute('open_demat_admin_user_index');
    }

    #[Route('/{id}/delete', name: 'open_demat_admin_user_delete', methods: ['POST'])]
    public function delete(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('delete_user_' . $user->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('open_demat_admin_user_index');
        }

        $currentUser = $this->getUser();
        if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
            $this->addFlash('warning', "Vous ne pouvez pas supprimer votre propre compte.");
            return $this->redirectToRoute('open_demat_admin_user_index');
        }

        $username = $user->getUsername();

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', sprintf('Utilisateur %s supprimé.', $username));

        return $this->redirectToRoute('open_demat_admin_user_index');
    }

    #[Route('/delete-bulk', name: 'open_demat_admin_user_delete_bulk', methods: ['POST'])]
    public function deleteBulk(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('delete_users_bulk', (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('open_demat_admin_user_index');
        }

        $ids = $request->request->all('ids');

        if (!is_array($ids) || empty($ids)) {
            $this->addFlash('info', 'Aucun utilisateur sélectionné pour suppression.');
            return $this->redirectToRoute('open_demat_admin_user_index');
        }

        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, static fn (int $id): bool => $id > 0);
        $ids = array_values(array_unique($ids));

        $currentUser = $this->getUser();
        $removed = 0;

        foreach ($ids as $id) {
            $user = $userRepository->find($id);

            if (!$user instanceof User) {
                continue;
            }

            if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
                continue;
            }

            $em->remove($user);
            $removed++;
        }

        if ($removed > 0) {
            $em->flush();
            $this->addFlash('success', sprintf('%d utilisateur(s) supprimé(s).', $removed));
        } else {
            $this->addFlash('info', "Aucun utilisateur n'a été supprimé.");
        }

        return $this->redirectToRoute('open_demat_admin_user_index');
    }
}
