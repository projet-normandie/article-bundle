<?php

namespace ProjetNormandie\ArticleBundle\Command;

use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ProjetNormandie\ArticleBundle\Repository\CommentRepository;
use ProjetNormandie\ArticleBundle\Filter\Bbcode as BbcodeFilter;

class CommentCommand extends Command
{
    protected static $defaultName = 'pn-article:comment';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pn-article:comment')
            ->setDescription('Command for a comment article')
            ->addArgument(
                'function',
                InputArgument::REQUIRED,
                'What do you want to do?'
            );
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool|int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $function = $input->getArgument('function');

        switch ($function) {
            case 'migrate':
                $this->migrate();
                break;
        }

        return 0;
    }


    /**
     *
     */
    private function migrate()
    {
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->em->getRepository('ProjetNormandie\ArticleBundle\Entity\Comment');

        $bbcodeFiler = new BbcodeFilter();
        $comments = $commentRepository->findAll();
        foreach ($comments as $comment) {
            $comment->setText($bbcodeFiler->filter($comment->getText()));
        }
        $this->em->flush();
    }
}
