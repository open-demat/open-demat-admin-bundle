<?php

declare(strict_types=1);

namespace OpenDemat\AdminBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use OpenDemat\Core\Entity\User;
use OpenDemat\Core\Tests\Helpers\TestUserFactory;

final class AdminBundleIntegrationTest extends WebTestCase
{
    private function em(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/');

        $this->assertResponseRedirects('/cas/login');
    }

    public function test_admin_dashboard_forbidden_without_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $user = TestUserFactory::createUser($em, 'simple-user');

        $client->loginUser($user, 'main');
        $client->request('GET', '/admin/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_admin_dashboard_accessible_with_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $admin = TestUserFactory::createUser($em, 'admin-dashboard', ['ROLE_ADMIN']);

        $client->loginUser($admin, 'main');
        $client->request('GET', '/admin/');

        $this->assertResponseIsSuccessful();
    }

    public function test_configuration_page_accessible_with_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $admin = TestUserFactory::createUser($em, 'configuration-admin-' . bin2hex(random_bytes(4)), ['ROLE_ADMIN']);

        $client->loginUser($admin, 'main');
        $client->request('GET', '/admin/configuration');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Configuration globale');
    }

    public function test_process_index_requires_authentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/process/');

        $this->assertResponseRedirects('/cas/login');
    }

    public function test_process_index_forbidden_without_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $user = TestUserFactory::createUser($em, 'simple-user-process');

        $client->loginUser($user, 'main');
        $client->request('GET', '/admin/process/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_process_index_accessible_with_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $admin = TestUserFactory::createUser($em, 'process-admin', ['ROLE_ADMIN']);

        $client->loginUser($admin, 'main');
        $client->request('GET', '/admin/process/');

        $this->assertResponseIsSuccessful();
    }

    public function test_referentiels_index_requires_authentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/referentiels/');

        $this->assertResponseRedirects('/cas/login');
    }

    public function test_referentiels_index_forbidden_without_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $user = TestUserFactory::createUser($em, 'simple-user-referentiel');

        $client->loginUser($user, 'main');
        $client->request('GET', '/admin/referentiels/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_referentiels_index_accessible_with_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $admin = TestUserFactory::createUser($em, 'referentiel-admin', ['ROLE_ADMIN']);

        $client->loginUser($admin, 'main');
        $client->request('GET', '/admin/referentiels/');

        $this->assertResponseIsSuccessful();
    }

    public function test_document_view_requires_authentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/attachments/document/1');

        $this->assertResponseRedirects('/cas/login');
    }

    public function test_document_view_forbidden_without_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $user = TestUserFactory::createUser($em, 'doc-user');

        $client->loginUser($user, 'main');
        $client->request('GET', '/admin/attachments/document/1');

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_attachment_index_forbidden_without_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $user = TestUserFactory::createUser($em, 'attachment-user');

        $client->loginUser($user, 'main');
        $client->request('GET', '/admin/attachments/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_attachment_index_accessible_with_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $admin = TestUserFactory::createUser($em, 'attachment-admin', ['ROLE_ADMIN']);

        $client->loginUser($admin, 'main');
        $client->request('GET', '/admin/attachments/');

        $this->assertResponseIsSuccessful();
    }

    public function test_user_index_forbidden_without_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $user = TestUserFactory::createUser($em, 'user-admin-page');

        $client->loginUser($user, 'main');
        $client->request('GET', '/admin/users/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_user_index_accessible_with_role_admin(): void
    {
        $client = static::createClient();
        $em = $this->em();

        $admin = TestUserFactory::createUser($em, 'user-admin', ['ROLE_ADMIN']);

        $client->loginUser($admin, 'main');
        $client->request('GET', '/admin/users/');

        $this->assertResponseIsSuccessful();
    }

    public function test_user_create_requires_authentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/admin/users/create');

        $this->assertResponseRedirects('/cas/login');
    }

    public function test_user_delete_prevents_self_delete(): void
    {
        $client = static::createClient();
        $em = $this->em();

        /** @var User $user */
        $user = TestUserFactory::createUser($em, 'self-delete-user', ['ROLE_ADMIN']);

        $client->loginUser($user, 'main');

        $crawler = $client->request('GET', '/admin/users/');
        $this->assertResponseIsSuccessful();

        $token = $this->extractDeleteTokenForUser($crawler->html(), $user->getId());

        $client->request('POST', '/admin/users/' . $user->getId() . '/delete', [
            '_token' => $token,
        ]);

        $this->assertResponseRedirects('/admin/users/');

        $em->clear();
        $stillExists = $em->getRepository(User::class)->find($user->getId());

        $this->assertNotNull($stillExists);
    }

    private function extractDeleteTokenForUser(string $html, int $userId): string
    {
        $pattern = sprintf(
            '#/admin/users/%d/delete.*?name="_token"\s+value="([^"]+)"#s',
            $userId
        );

        $this->assertMatchesRegularExpression($pattern, $html);

        preg_match($pattern, $html, $matches);

        return $matches[1];
    }
}
