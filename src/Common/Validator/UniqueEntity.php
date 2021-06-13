<?php

declare(strict_types=1);

namespace App\Common\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class UniqueEntity extends Constraint
{
    public const NOT_UNIQUE_ERROR = 'd8eecc54-f6fb-40c0-a9af-89ed7e843ce9';

    public string $message = 'This value is already used.';

    /**
     * @var string[]|string
     */
    public array|string $fields;

    public string $repositoryMethod = 'count';
    public ?string $errorPath = null;

    /**
     * {@inheritDoc}
     */
    protected static $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    /**
     * {@inheritDoc}
     */
    public function __set(string $option, $value)
    {
        if ('fields' === $option) {
            $value = (array) $value;

            if (0 === \count($value)) {
                throw new ConstraintDefinitionException('The "fields" option must contain at least one value.');
            }
        }

        parent::__set($option, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOption(): ?string
    {
        return 'fields';
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions(): array
    {
        return ['fields'];
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets(): string|array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
