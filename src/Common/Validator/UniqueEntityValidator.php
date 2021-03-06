<?php

declare(strict_types=1);

namespace App\Common\Validator;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\ValidatorException;

class UniqueEntityValidator extends ConstraintValidator
{
    private const ENTITY_ID = 4;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, UniqueEntity::class);
        }

        if (null === $value) {
            return;
        } elseif (!\is_object($value)) {
            throw new UnexpectedValueException($value, 'object');
        }

        $class = $this->getEntityClass($value);
        $criteria = $this->buildCriteria($value, $class, $constraint->fields);
        $count = $this->executeCountQuery($class, $constraint->repositoryMethod, $criteria);

        if (0 === $count) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->atPath($this->getErrorPath($constraint))
            ->setParameter('{{ value }}', $this->formatCriteria($criteria))
            ->setInvalidValue($value)
            ->setPlural(\count($criteria))
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->setCause($count)
            ->addViolation();
    }

    /**
     * {@inheritDoc}
     */
    protected function formatValue(mixed $value, int $format = 0): string
    {
        if (!\is_object($value)
            || (($format & self::PRETTY_DATE) && $value instanceof \DateTimeInterface)
            || (($format & self::OBJECT_TO_STRING) && \is_callable([$value, '__toString']))
        ) {
            return parent::formatValue($value, $format);
        }

        $class = ClassUtils::getClass($value);
        $metadataFactory = $this->entityManager->getMetadataFactory();

        if (!($format & self::ENTITY_ID) || !$metadataFactory->hasMetadataFor($class)) {
            return \sprintf('object("%s")', $class);
        }

        $ids = $metadataFactory->getMetadataFor($class)->getIdentifierValues($value);
        $formattedIds = $this->formatValues($ids, $format & ~self::ENTITY_ID);

        return \sprintf('object("%s") identified by (%s)', $class, $formattedIds);
    }

    /**
     * {@inheritDoc}
     */
    protected function formatValues(array $values, int $format = 0): string
    {
        foreach ($values as $key => $value) {
            $values[$key] = \sprintf('%s: %s', $key, $this->formatValue($value, $format));
        }

        return \implode(', ', $values);
    }

    /**
     * @throws ValidatorException
     */
    private function getEntityClass(object $value): string
    {
        $class = ClassUtils::getClass($value);
        if ($this->entityManager->getMetadataFactory()->isTransient($class)) {
            throw new ValidatorException(\sprintf('Expected an entity, object of "%s" class given.', $class));
        }

        return $class;
    }

    /**
     * @param string[] $fields
     *
     * @throws ConstraintDefinitionException
     */
    private function buildCriteria(object $value, string $class, array $fields): array
    {
        $metadata = $this->entityManager->getClassMetadata($class);
        $criteria = [];

        foreach ($fields as $field) {
            if (!$metadata->hasField($field) && !$metadata->hasAssociation($field)) {
                throw new ConstraintDefinitionException(\sprintf(
                    'No mapped field or association "%s" exists on entity "%s".',
                    $field,
                    $class
                ));
            }

            $criteria[$field] = $metadata->getFieldValue($value, $field);

            if ($metadata->hasAssociation($field)) {
                $this->entityManager->initializeObject($criteria[$field]);
            }
        }

        return $criteria;
    }

    /**
     * @throws ConstraintDefinitionException
     * @throws UnexpectedTypeException
     */
    private function executeCountQuery(string $class, string $repositoryMethod, array $criteria): int
    {
        $repository = $this->entityManager->getRepository($class);
        if (!\is_callable([$repository, $repositoryMethod])) {
            throw new ConstraintDefinitionException(\sprintf(
                'No callable method "%s" found on repository "%s".',
                $repositoryMethod,
                \get_class($repository)
            ));
        }

        $count = \call_user_func([$repository, $repositoryMethod], $criteria);
        if (!\is_int($count)) {
            throw new UnexpectedTypeException($count, 'int');
        }

        return $count;
    }

    private function getErrorPath(UniqueEntity $constraint): string
    {
        if (null !== $constraint->errorPath) {
            return $constraint->errorPath;
        }

        return 1 === \count($constraint->fields) ? \reset($constraint->fields) : '';
    }

    private function formatCriteria(array $criteria): string
    {
        $format = self::PRETTY_DATE | self::OBJECT_TO_STRING | self::ENTITY_ID;

        return 1 === \count($criteria)
            ? $this->formatValue(\reset($criteria), $format)
            : $this->formatValues($criteria, $format);
    }
}
