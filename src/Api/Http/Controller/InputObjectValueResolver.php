<?php

declare(strict_types=1);

namespace App\Api\Http\Controller;

use App\Api\Http\RequestReader;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Deserializes API requests content into input objects used as controller arguments.
 *
 * The deserialization can be disabled by setting the "_api_receive" request attribute to false for an endpoint (via the
 * route's "defaults" configuration).
 *
 * Other request attributes used as configuration options:
 *      - "_api_receive_class": explicitly specifies the input object class. Useful for example when the input is an
 *        array of objects belonging to some class (e.g. App\SomeClass[]) and the serializer must know this class to
 *        perform a proper deserialization
 */
class InputObjectValueResolver implements ArgumentValueResolverInterface
{
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
        $requestAttributes = $request->attributes;

        return $requestAttributes->getBoolean('_api_endpoint')
            && $requestAttributes->getBoolean('_api_receive', true)
            && null !== $this->getInputClass($requestAttributes, $argument)
            // A request's content is deserialized into a single input object.
            && !isset($this->resolvedRequests[$request]);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $inputClass = $this->getInputClass($request->attributes, $argument);
        $inputObject = $this->requestReader->read($request, $inputClass);

        $this->resolvedRequests[$request] = true;

        return $inputObject;
    }

    /**
     * @param ParameterBag     $requestAttributes
     * @param ArgumentMetadata $argumentMetadata
     *
     * @return string|null
     */
    private function getInputClass(ParameterBag $requestAttributes, ArgumentMetadata $argumentMetadata): ?string
    {
        if ($requestAttributes->has('_api_receive_class')) {
            return $requestAttributes->get('_api_receive_class');
        }

        $inputClass = $argumentMetadata->getType();

        return null !== $inputClass && \class_exists($inputClass) ? $inputClass : null;
    }
}
