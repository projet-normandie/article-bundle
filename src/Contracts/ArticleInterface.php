<?php
namespace ProjetNormandie\ArticleBundle\Contracts;

interface ArticleInterface
{
    public const STATUS_UNDER_CONSTRUCTION= 'UNDER CONSTRUCTION';
    public const STATUS_PUBLISHED = 'PUBLISHED';
    public const STATUS_CANCELED = 'CANCELED';
}
