<?php

declare(strict_types=1);

namespace App\Api\Http\Controller;

use App\Api\Http\ApiEndpointsConfigurationTrait;
use App\Api\Http\RequestReader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Deserializes API requests content into input objects used as controller arguments.
 *
 * The deserialization can be disabled by setting the "_api_receive" request attribute to false for an endpoint (via the
 * route's "defaults" configuration).
 *
 * Other request attributes used as configuration options:
 *      - "_api_receive_argument": explicitly specifies the input object argument name. If not specified, the first
 *        argument being resolved will be considered
 *      - "_api_receive_class": explicitly specifies the input object class. Useful for example when the input is an
 *        array of objects belonging to some class (e.g. App\SomeClass[]) and the serializer must know this class to
 *        perform a proper deserialization
 *      - "_api_receive_deserialization_groups": additional groups to use when deserializing requests. The default
 *        deserialization group is "api_request" (provided by {@see RequestReader})
 *      - "_api_receive_deserialization_context": additional context to pass when deserializing requests
 */
class InputObjectValueResolver implements ArgumentValueResolverInterface
{
    use ApiEndpointsConfigurationTrait;

    private RequestReader $requestReader;
    private \SplObjectStorage $resolvedRequests;

    /**
     * @param RequestReader $requestReader
     */
    public function __construct(RequestReader $requestReader)
    {
        $this->requestReader = $requestReader;
        $this->resolvedRequests = new \SplObjectStorage();
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $this->isApiReceiveEnabled($request)
            && $this->isInputObjectArgument($request, $argument)
            && null !== $this->getInputClass($request, $argument)
            // A single argument should be resolved to the input object associated to a request.
            && !isset($this->resolvedRequests[$request]);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $inputClass = $this->getInputClass($request, $argument);
        $context = [AbstractNormalizer::GROUPS => $this->getApiSetting($request, 'receive_deserialization_groups', [])]
            + $this->getApiSetting($request, 'receive_deserialization_context', []);

        $inputObject = $this->requestReader->read($request, $inputClass, $context);

        $this->resolvedRequests[$request] = true;

        yield $inputObject;
    }

    /**
     * @param Request          $request
     * @param ArgumentMetadata $argumentMetadata
     *
     * @return bool
     */
    private function isInputObjectArgument(Request $request, ArgumentMetadata $argumentMetadata): bool
    {
        return !$this->hasApiSetting($request, 'receive_argument')
            || $this->getApiSetting($request, 'receive_argument') === $argumentMetadata->getName();
    }

    /**
     * @param Request          $request
     * @param ArgumentMetadata $argumentMetadata
     *
     * @return string|null
     *
     * @throws \LogicException
     */
    private function getInputClass(Request $request, ArgumentMetadata $argumentMetadata): ?string
    {
        if ($this->hasApiSetting($request, 'receive_class')) {
            return $this->getApiSetting($request, 'receive_class');
        }

        $inputClass = $argumentMetadata->getType();
        if (null === $inputClass || !\class_exists($inputClass)) {
            if ($this->hasApiSetting($request, 'receive_argument')) {
                // If a specific argument was pointed to be the input object but its class could not be determined, then
                // we are dealing with misconfiguration.
                throw new \LogicException(\sprintf(
                    'The class of input object argument "%s" could not be determined. '
                    .'Either type hint the argument or specify it via the "_api_receive_class" request attribute.',
                    $argumentMetadata->getName()
                ));
            }

            return null;
        }

        return $inputClass;
    }
}
