<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Component\DomCrawler\Crawler;

class SecurityControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/UserFixtures.yaml',
        ]);
    }

    private function getAdminUser(): User
    {
        return self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@test.com']);
    }
    private function getEditorUser(): User
    {
        return self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'editor@test.com']);
    }

    public function testResponseLoginPage(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testLoginFormWithGoodCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'admin@test.com',
            '_password' => 'Test1234!',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/');
    }

    public function testLoginFormWithBadCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'admin@test.com',
            '_password' => 'badpassword',
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Identifiants invalides.');
    }

    public function testAdminUserPageWithNotConnected(): void
    {
        $this->client->request('GET', '/admin/users');
        $this->assertResponseRedirects('/login');
    }

    public function testAdminUserPageWithAdminUser(): void
    {
        $this->client->loginUser($this->getAdminUser());
        $this->client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAdminUserPageWithEditorUser(): void
    {
        $this->client->loginUser($this->getEditorUser());
        $this->client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(403);
    }

    //Test Register

    public function testResponseRegisterPage(): void
    {
        $this->client->request('GET', '/register');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testRegisterFormWithGoodCredentials(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton("S'inscrire")->form([
            'user[firstName]' => 'Test',
            'user[lastName]' => 'Test1',
            'user[email]' => 'test1234@test.com',
            'user[password][first]' => 'Test1234!',
            'user[password][second]' => 'Test1234!',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/login');
    }

    public function testRegisterFormWithBadPassword(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton("S'inscrire")->form([
            'user[firstName]' => 'Test',
            'user[lastName]' => 'Test1',
            'user[email]' => 'test1234@test.com',
            'user[password][first]' => 'Test1234!',
            'user[password][second]' => 'badpassword',
        ]);
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('.invalid-feedback.d-block', 'Les mots de passe doivent être identiques.');
    }
    public function testRegisterFormWithBadEmail(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton("S'inscrire")->form([
            'user[firstName]' => 'Test',
            'user[lastName]' => 'Test1',
            'user[email]' => 'test1234@test',
            'user[password][first]' => 'Test1234!',
            'user[password][second]' => 'Test1234!',
        ]);
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('.invalid-feedback.d-block', "Cette valeur n'est pas une adresse email valide.");
    }

    public function testRegisterFlushInDataBase(): void
    {
        $crawler = $this->client->request("GET", "/register");
        $form = $crawler->selectButton("S'inscrire")->form([
            'user[firstName]' => 'Admin',
            'user[lastName]' => 'User',
            'user[email]' => 'admin123@test.com',
            'user[password][first]' => 'Test1234!',
            'user[password][second]' => 'Test1234!',
        ]);
        //Je test si l'utilisateur à bien été envoyé en base de donnée
        $this->client->submit($form);
        $this->assertResponseRedirects('/login');
        $user = $this->getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin123@test.com']);
        $this->assertNotNull($user);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->databaseTool = null;
        $this->client = null;
    }
}
