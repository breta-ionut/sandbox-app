<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('validator.mapping.cache.file', param('kernel.cache_dir').'/validation.php');

    $services = $container->services();

    $services->set(ContainerConstraintValidatorFactory::class)
        ->args([abstract_arg('constraint validators container')]);

    $services->alias(ConstraintValidatorFactoryInterface::class, ContainerConstraintValidatorFactory::class);

    $services->set('validator.mapping.cache', CacheItemPoolInterface::class)
        ->factory([PhpArrayAdapter::class, 'create'])
        ->args([param('validator.mapping.cache.file'), service('cache.validator')]);

    $services->set(ValidatorBuilder::class)
        ->factory([Validation::class, 'createValidatorBuilder'])
        ->call('enableAnnotationMapping', [true])
        ->call('setConstraintValidatorFactory', [service(ConstraintValidatorFactoryInterface::class)]);

    $services->set(ValidatorInterface::class)
        ->factory([service(ValidatorBuilder::class), 'getValidator']);

    // Constraint validators.
    $services->set(EmailValidator::class)
        ->args([abstract_arg('default mode')])
        ->tag('validator.constraint_validator');
    // End of - Constraint validators.
};
