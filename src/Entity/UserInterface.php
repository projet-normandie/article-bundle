<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Entity;

interface UserInterface
{
    public function getId(): ?int;
    public function getUsername(): string;
}
