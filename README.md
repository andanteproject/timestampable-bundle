![Andante Project Logo](https://github.com/andanteproject/timestampable-bundle/blob/main/andanteproject-logo.png?raw=true)
# Timestampable Bundle 
#### Symfony Bundle - [AndanteProject](https://github.com/andanteproject)
[![Latest Version](https://img.shields.io/github/release/andanteproject/timestampable-bundle.svg)](https://github.com/andanteproject/timestampable-bundle/releases)
![Github actions](https://github.com/andanteproject/timestampable-bundle/actions/workflows/workflow.yml/badge.svg?branch=main)
![Framework](https://img.shields.io/badge/Symfony-4.x|5.x|6.x|7.x-informational?Style=flat&logo=symfony)
![Php8](https://img.shields.io/badge/PHP-%208.x-informational?style=flat&logo=php)
![PhpStan](https://img.shields.io/badge/PHPStan-Level%208-syccess?style=flat&logo=php) 

A Symfony Bundle to handle entities createdAt and updatedAt dates with Doctrine. 🕰 

## Requirements
Symfony 4.x-7.x and PHP 8.2.

## Install
Via [Composer](https://getcomposer.org/):
```bash
$ composer require andanteproject/timestampable-bundle
```

## Features
- No configuration required to be ready to go but fully customizabile;
- `createdAt` and `updatedAt` properties are `?\DateTimeImmutable`;
- Uses [Symfony Clock](https://symfony.com/doc/current/components/clock.html);
- Does not override your `createdAt` and `updatedAt` values when you set them explicitly;
- No annotation/attributes required;
- Works like magic ✨.

## Basic usage
After [install](#install), make sure you have the bundle registered in your symfony bundles list (`config/bundles.php`):
```php
return [
    /// bundles...
    Andante\TimestampableBundle\AndanteTimestampableBundle::class => ['all' => true],
    /// bundles...
];
```
This should have been done automagically if you are using [Symfony Flex](https://flex.symfony.com). Otherwise, just register it by yourself.


Let's suppose we have a `App\Entity\Article` doctrine entity we want to track created and update dates.
All you have to do is to implement `Andante\TimestampableBundle\Timestampable\TimestampableInterface` and use `Andante\TimestampableBundle\Timestampable\TimestampableTrait` trait.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Andante\TimestampableBundle\Timestampable\TimestampableInterface;
use Andante\TimestampableBundle\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity()
 */
class Article implements TimestampableInterface // <-- implement this
{
    use TimestampableTrait; // <-- add this

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;
    
    public function __construct(string $title)
    {
        $this->title = $title;
    }
    
    // ...
    // Some others beautiful properties and methods ...
    // ...
}
```
Make sure to update you database schema following your doctrine workflow (`bin/console doctrine:schema:update --force` if you are a badass devil guy or with a [migration](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.0/reference/introduction.html) if you choosed the be a better developer!).

You shoud see a new columns named `created_at` and `updated_at` ([can i change this?](#configuration-completely-optional)) or something similar based on your [doctrine naming strategy](https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/namingstrategy.html). 

#### Congrats! You're done! 🎉

Remember that `TimestampableInterface` and `TimestampableTrait` are shortcut to use `CreatedAtTimestampableInterface`+`CreatedAtTimestampableTrait` and `UpdatedAtTimestampableInterface`+`UpdatedAtTimestampableTrait` at the same time!
If you need to track only **create date** or **update date** you can use these more specific interfaces!

| To keep track of | Implement interface | Use this Trait | 
| --- | --- | --- |
| **Create date** | `Andante\TimestampableBundle\Timestampable\CreatedAtTimestampableInterface` | `Andante\TimestampableBundle\Timestampable\CreatedAtTimestampableTrait` |
| **Update date** | `Andante\TimestampableBundle\Timestampable\UpdatedAtTimestampableInterface` | `Andante\TimestampableBundle\Timestampable\UpdatedAtTimestampableTrait` |
| **Both create and update dates** | `Andante\TimestampableBundle\Timestampable\TimestampableInterface` | `Andante\TimestampableBundle\Timestampable\TimestampableTrait` |

## Usage with no trait
```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Andante\TimestampableBundle\Timestampable\TimestampableInterface;

/**
 * @ORM\Entity()
 */
class Article implements TimestampableInterface // <-- implement this
{
    // No trait needed
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $title;
    
    // DO NOT use ORM annotations to map these properties. See bundle configuration section for more info 
    private ?\DateTimeImmutable $createdAt = null; 
    private ?\DateTimeImmutable $updatedAt = null; 
    
    public function __construct(string $title)
    {
        $this->title = $title;
    }
    
    public function setCreatedAt(\DateTimeImmutable $dateTime): void
    {
        $this->createdAt = $dateTime;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function setUpdatedAt(\DateTimeImmutable $dateTime): void
    {
        $this->updatedAt = $dateTime;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
```
This allows you to, for instance, to have **a different name** for your properties (E.g. `created` instead of `createdAt` and `updated` instead of `updatedAt`).
But you will need to explicit this in [bundle configuration](#configuration-completely-optional).

## Configuration (completely optional)
This bundle is build thinking how to save you time and follow best practices as close as possible.

This means you can even ignore to have a `andante_timestampable.yml` config file in your application.

However, for whatever reason (legacy code?), use the bundle configuration to change most of the behaviors as your needs.
```yaml
andante_timestampable:
  default:
    created_at_property_name: createdAt # default: createdAt
                                        # The property to be used by default as createdAt date inside entities 
                                        # implementing CreatedAtTimestampableInterface or TimestampableInterface
    updated_at_property_name: updatedAt # default: updatedAt
                                        # The property to be used by default as updatedAt date inside entities 
                                        # implementing UpdatedAtTimestampableInterface or TimestampableInterface

    created_at_column_name: created_at # default: null
                                       # Column name to be used on database for create date. 
                                       # If set to NULL will use your default doctrine naming strategy
    updated_at_column_name: updated_at # default: null
                                       # Column name to be used on database for update date. 
                                       # If set to NULL will use your default doctrine naming strategy
  entity: # You can use per-entity configuration to override default config
    Andante\TimestampableBundle\Tests\Fixtures\Entity\Organization:
      created_at_property_name: createdAt
    Andante\TimestampableBundle\Tests\Fixtures\Entity\Address:
      created_at_property_name: created
      updated_at_property_name: updated
      created_at_column_name: created_date
      updated_at_column_name: updated_date
```

Built with love ❤️ by [AndanteProject](https://github.com/andanteproject) team.
