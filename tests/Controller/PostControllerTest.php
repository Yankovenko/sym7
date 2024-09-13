<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class PostControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/post/';

    /**
     * @throws ExceptionInterface
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $application = new Application(static::createKernel());

        $command = $application->find('doctrine:database:drop');
        $command->run(new ArrayInput(['--env'=>'test', '--force' => true]), new NullOutput());

        $command = $application->find('doctrine:database:create');
        $command->run(new ArrayInput(['--env'=>'test']), new NullOutput());

        $command = $application->find('doctrine:schema:create');
        $command->run(new ArrayInput(['--env'=>'test']), new NullOutput());

    }

    /**
     * @throws ExceptionInterface
     */
    protected function setUp(): void
    {

        $this->client = static::createClient();

        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Post::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();

    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Post index');
        self::assertSame('Post index', $crawler->filter('body')->filter('h1')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'post_create[title]' => 'Test Title',
            'post_create[content]' => 'Content',
        ]);

        self::assertSame(1, $this->repository->count());
    }

    public function testShow(): void
    {
        $fixture = new Post();
        $fixture->setTitle('Title123');
        $fixture->setContent('Content123');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Post');

        self::assertAnySelectorTextContains('td', 'Title123');
        self::assertAnySelectorTextContains('td', 'Content123');
    }

    public function testEdit(): void
    {
        $fixture = new Post();
        $fixture->setTitle('Value');
        $fixture->setContent('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'post_edit[title]' => 'Something New',
            'post_edit[content]' => 'Something New',
        ]);

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getContent());
    }

    public function testRemove(): void
    {
        $fixture = new Post();
        $fixture->setTitle('Value');
        $fixture->setContent('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/post');
        self::assertSame(0, $this->repository->count());
    }
}
