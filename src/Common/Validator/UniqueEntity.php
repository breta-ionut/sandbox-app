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
     * @param string[]|null $groups
     *
     * @throws ConstraintDefinitionException
     */
    public function __construct(array|string $options = null, array $groups = null, mixed $payload = null)
    {
        if (\is_string($options)) {
            $options = [$options];
        }

        parent::__construct($options, $groups, $payload);

        if (0 === \count($this->fields)) {
            throw new ConstraintDefinitionException('The "fields" option must contain at least one value.');
        }
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
