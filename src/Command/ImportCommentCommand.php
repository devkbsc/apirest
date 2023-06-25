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
use App\Entity\Comment;

class ImportCommentCommand extends Command
{
    protected static $defaultName = 'app:import-comment';
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importComment();
        $output->writeln('Comments Json data imported successfully.');

        return 0;
    }


    public function importComment(){
        
        $client = new Client(['verify' => false]);
        $commentResponse = $client->request('GET', 'https://jsonplaceholder.typicode.com/comments');
        $commentContent = $commentResponse->getBody()->getContents();
        $commentData = json_decode($commentContent, true);

        foreach ($commentData as $item) {

            $comment = new Comment();
            $comment->setId($item['id']);
            $comment->setName($item['name']);
            $comment->setEmail($item['email']);
            $comment->setBody($item['body']);
            $comment->setPost($item['postId']);
            
            $this->entityManager->persist($comment);
        }

        $this->entityManager->flush();

    }
}
