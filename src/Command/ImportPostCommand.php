<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use App\Entity\Post;

class ImportPostCommand extends Command
{
    protected static $defaultName = 'app:import-post';
    protected static $defaultDescription = 'Add a short description for your command';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();

    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client(['verify' => false]);
        $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/posts');
        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);

        foreach ($data as $item) {
            $post = new Post();
            $post->setId($item['id']);
            $post->setTitle($item['title']);
            $post->setBody($item['body']);
            $post->setUserId($item['userId']);
            $this->entityManager->persist($post);
        }
        $this->entityManager->flush();

        $output->writeln('Post Json data imported successfully.');

        return 0;
    }
}
