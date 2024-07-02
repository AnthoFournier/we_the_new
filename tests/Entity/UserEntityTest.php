<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Traits\TestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserEntityTest extends KernelTestCase
{
    use TestTrait;
    protected ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRepositoryCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/UserFixtures.yaml'
        ]);

        $userRepo = self::getContainer()->get(UserRepository::class);
        $users = $userRepo->findAll();
        $this->assertCount(12, $users);
    }

    private function getEntity(): User
    {
        return (new User)
            ->setEmail('test@test.com')
            ->setFirstName('Test')
            ->setLastName('Test')
            ->setPassword('test');
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    /**
     * @dataProvider provideEmail
     *
     * @param string $email
     * @param integer $number
     * @return void
     */
    public function testInvalidEmail(string $email, int $number): void
    {
        $user = $this->getEntity()
            ->setEmail($email);

        $this->assertHasErrors($user, $number);
    }

    public function provideEmail(): array
    {
        return [
            'non_unique' => [
                'email' => 'admin@test.com',
                'number' => 1,
            ],
            'max_length' => [
                'email' => str_repeat('a', 180) . '@test.com',
                'number' => 1,
            ],
            'empty' => [
                'email' => '',
                'number' => 1,
            ],
            'invalid_email' => [
                'email' => 'test.com',
                'number' => 1,
            ],
        ];
    }

    /**
     * @dataProvider provideName
     *
     * @param string $name
     * @return void
     */
    public function testInvalidFirstName(string $name): void
    {
        $user = $this->getEntity()
            ->setFirstName($name);

        $this->assertHasErrors($user, 1);
    }

    public function provideName(): array
    {
        return [
            'max_length' => [
                'name' => str_repeat('a', 256),
            ],
            'empty' => [
                'name' => '',
            ],
        ];
    }

    /**
     * @dataProvider provideName
     *
     * @param string $name
     * @return void
     */
    public function testInvalidLastName(string $name): void
    {
        $user = $this->getEntity()
            ->setLastName($name);

        $this->assertHasErrors($user, 1);
    }

    /**
     * @dataProvider providePhone
     *
     * @param string $phone
     * @param integer $number
     * @return void
     */
    public function testInvalidPhone(string $phone, int $number): void
    {
        $user = $this->getEntity()
            ->setPhone($phone);

        $this->assertHasErrors($user, $number);
    }

    public function providePhone(): array
    {
        return [
            'max_length' => [
                'phone' => str_repeat('a', 11),
                'number' => 1,
            ],
        ];
    }

    public function testFindPaginationOrderByDate(): void
    {
        // $this->databaseTool->loadAliceFixture([
        //     \dirname(__DIR__) . '/Fixtures/UserFixtures.yaml'
        // ]);

        $repo = self::getContainer()->get(UserRepository::class);
        $users = $repo->findPaginateOrderByDate(9, 1);
        $this->assertCount(9, $users);
    }

    public function testFindPaginateOrderDateWithSearch(): void
    {
        $repo = self::getContainer()->get(UserRepository::class);
        $users = $repo->findPaginateOrderByDate(9, 1, 'admin');
        $this->assertCount(1, $users);
    }

    public function testFindPaginateOrderDateWithInvalidArgument(): void
    {
        $repo = self::getContainer()->get(UserRepository::class);

        $this->expectException(\TypeError::class);

        $repo->findPaginateOrderByDate('test', 1, 'invalid');
    }

    public function tearDown(): void
    {
        $this->databaseTool = null;
        parent::tearDown();
    }
}
