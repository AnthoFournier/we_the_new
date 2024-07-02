<?php


namespace App\Tests\Entity;

use App\Entity\Product\Gender;
use App\Tests\Traits\TestTrait;
use App\Repository\Product\GenderRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;

class GenderEntityTest extends KernelTestCase
{
    use TestTrait;

    private ?ORMDatabaseTool $databaseTool = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRepositoryCount(): void
    {
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/GenderFixtures.yaml'
        ]);

        $genderRepo = self::getContainer()->get(GenderRepository::class);
        $genders = $genderRepo->findAll();
        $this->assertCount(1, $genders);
    }

    private function getEntity(): Gender
    {
        return (new Gender)
            ->setName('Test')
            ->setEnable(true);
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    /**
     * @dataProvider provideName
     *
     * @param string $name
     * @return void
     */
    public function testInvalidName(string $name): void
    {
        $this->assertHasErrors($this->getEntity()->setName($name), 1);
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

    public function tearDown(): void
    {
        parent::tearDown();

        $this->databaseTool = null;
    }
}
