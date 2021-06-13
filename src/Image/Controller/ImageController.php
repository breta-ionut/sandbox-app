<?php

declare(strict_types=1);

namespace App\Image\Controller;

use App\Api\Exception\ResourceNotFoundException;
use App\Api\Exception\ValidationException;
use App\Api\Http\View;
use App\Core\Controller\AbstractController;
use App\Image\Image\ImageManager;
use App\Image\Model\Image;
use App\Image\Repository\ImageRepository;
use App\Image\Storage\ImageStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(name: 'image_')]
class ImageController extends AbstractController
{
    /**
     * @throws ResourceNotFoundException
     */
    #[Route('/{token}/{style}', name: 'get', methods: 'GET')]
    public function getImage(
        ImageRepository $imageRepository,
        ImageStorage $imageStorage,
        string $token,
        string $style = null,
    ): RedirectResponse {
        $image = $imageRepository->findOneByToken($token);
        if (null === $image) {
            throw new ResourceNotFoundException(Image::class, $token);
        }

        $original = null === $style;

        $imageStorage->publish($image, $style);
        $imageStorage->setPublicUrls($image, $original, $original ? [] : [$style]);

        $publicUrl = $original ? $image->getOriginalPublicUrl() : $image->getPublicUrlForStyle($style);

        return new RedirectResponse($publicUrl, RedirectResponse::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @throws ValidationException
     */
    #[Route(name: 'upload', defaults: ['_api_receive' => false], methods: 'POST')]
    public function upload(Request $request, ValidatorInterface $validator, ImageManager $imageManager): View
    {
        $files = $request->files->all();
        $image = new Image(\reset($files) ?: null);

        $violations = $validator->validate($image);
        if (0 !== \count($violations)) {
            throw new ValidationException($violations);
        }

        $imageManager->upload($image);

        return new View($image, Response::HTTP_CREATED);
    }
}
