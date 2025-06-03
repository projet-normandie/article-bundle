<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ProjetNormandie\ArticleBundle\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * StateProcessor pour gérer la sécurité des commentaires selon l'environnement
 */
readonly class CommentSecurityProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security,
        private string $environment = 'prod'
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($this->environment !== 'test' && $data instanceof Comment) {
            $this->checkSecurity($data, $operation, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function checkSecurity(Comment $comment, Operation $operation, array $context): void
    {
        $operationName = $operation->getName();

        // Vérifier l'authentification pour POST et PUT
        if (in_array($operationName, ['post', 'put'])) {
            if (!$this->security->isGranted('ROLE_USER')) {
                throw new AccessDeniedException('Authentication required to manage comments');
            }
        }

        // Pour PUT, vérifier que l'utilisateur peut modifier ce commentaire
        if ($operationName === 'put') {
            $currentUser = $this->security->getUser();

            // Si c'est une création depuis la base, récupérer l'entité existante
            if (!$comment->getUser() && isset($context['previous_data'])) {
                $previousComment = $context['previous_data'];
                if ($previousComment instanceof Comment && $previousComment->getUser() !== $currentUser) {
                    throw new AccessDeniedException('You can only modify your own comments');
                }
            } elseif ($comment->getUser() && $comment->getUser() !== $currentUser) {
                throw new AccessDeniedException('You can only modify your own comments');
            }
        }

        // Pour POST, s'assurer que l'utilisateur est défini
        if ($operationName === 'post' && !$comment->getUser()) {
            $comment->setUser($this->security->getUser());
        }
    }
}
