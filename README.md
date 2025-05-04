# ProjetNormandie ArticleBundle

A Symfony bundle for managing multilingual articles with comments, integrated with Sonata Admin and API Platform.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/projet-normandie/article-bundle/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/projet-normandie/article-bundle/?branch=develop)
[![Build Status](https://scrutinizer-ci.com/g/projet-normandie/article-bundle/badges/build.png?b=develop)]()


## Features

- **Multilingual Articles**: Full support for multiple languages using A2lix TranslationFormBundle
- **Comments System**: Allows users to comment on articles with user authentication
- **Admin Interface**: Complete Sonata Admin integration for managing articles and comments
- **API Platform**: RESTful API endpoints for articles and comments
- **Status Management**: Articles can have different statuses (UNDER CONSTRUCTION, PUBLISHED, CANCELED)
- **Automatic Slug Generation**: Creates SEO-friendly URLs automatically
- **Timestamps**: Automatic tracking of creation and update times
- **User Management**: Integration with security system for authors and commenters

## Requirements

- PHP 8.3+
- Symfony 6.4+ or 7.0+
- Doctrine ORM
- Sonata Admin Bundle
- API Platform
- A2lix Translation Form Bundle
- Symfony Security Bundle

## Installation

1. Install the bundle via Composer:

```bash
composer require projet-normandie/article-bundle
```

2. Enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    ProjetNormandie\ArticleBundle\ProjetNormandieArticleBundle::class => ['all' => true],
];
```

3. Import the bundle configuration in your `config/packages/` directory.

## Configuration

The bundle auto-configures most services. Key services include:

- **Admin Classes**: `ArticleAdmin` and `CommentAdmin` for Sonata Admin
- **Event Listeners**: For articles, article translations, and comments
- **API Resources**: REST endpoints for articles and comments
- **Doctrine Extensions**: Translation support for API queries

## Usage

### Article Management

Articles support multiple languages and three statuses:

```php
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;

$article = new Article();
$article->setStatus(ArticleStatus::PUBLISHED);
$article->setTitle('My Article', 'en');
$article->setTitle('Mon Article', 'fr');
$article->setText('Content in English', 'en');
$article->setText('Contenu en français', 'fr');
```

### Using the Article Builder

The bundle provides a builder for creating articles:

```php
$builder = $container->get('pn.article.builder.article');
$builder
    ->setAuthor($user)
    ->setTitle('Title', 'en')
    ->setText('Content', 'en')
    ->send();
```

### API Endpoints

The bundle exposes the following REST API endpoints:

#### Articles
- `GET /api/articles` - List all articles
- `GET /api/articles/{id}` - Get a specific article
- `GET /api/articles/{id}/comments` - Get comments for an article

#### Comments
- `GET /api/article_comments` - List all comments
- `GET /api/article_comments/{id}` - Get a specific comment
- `POST /api/article_comments` - Create a new comment (requires authentication)
- `PUT /api/article_comments/{id}` - Update a comment (requires ownership)

### Admin Interface

Access the admin interface at:
- `/admin/article/list` - Article management
- `/admin/comment/list` - Comment management

## Key Components

### Entities

1. **Article**: Main article entity with multilingual support
2. **ArticleTranslation**: Stores translations for articles
3. **Comment**: Comments on articles
4. **UserInterface**: Interface for user integration

### Event Listeners

- **ArticleListener**: Handles author assignment and slug generation
- **ArticleTranslationListener**: Updates article timestamps on translation changes
- **CommentListener**: Manages comment count and user assignment

### Value Objects

- **ArticleStatus**: Manages article status values (UNDER_CONSTRUCTION, PUBLISHED, CANCELED)

## Translations

The bundle includes translations for:
- English (en)
- French (fr)

Add more languages by creating files in `src/Resources/translations/`.

## Form Types

- **RichTextEditorType**: Custom form type for rich text editing

## Security

- Creating comments requires `ROLE_USER`
- Updating comments requires ownership verification
- Admin access follows Sonata Admin security configuration

## Extending the Bundle

### Custom User Entity

Configure Doctrine to resolve the interface to your entity in `config/packages/doctrine.yaml`:

```yaml
doctrine:
    orm:
        resolve_target_entities:
            ProjetNormandie\ArticleBundle\Entity\UserInterface: App\Entity\User # or your User entity namespace
```

Or if you're using another bundle:

```yaml
doctrine:
    orm:
        resolve_target_entities:
            ProjetNormandie\ArticleBundle\Entity\UserInterface: ProjetNormandie\UserBundle\Entity\User
```

## License

This bundle is open-source software licensed under the MIT license.

## Support

For issues and feature requests, please use the GitHub issue tracker.

## Credits

Developed by Projet Normandie team.