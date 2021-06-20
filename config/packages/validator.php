<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Validator\Constraints\Email;

return static function (ContainerConfigurator $container): void {
    $container->extension('validator', [
        'email_validation_mode' => Email::VALIDATION_MODE_HTML5,
    ]);
};
