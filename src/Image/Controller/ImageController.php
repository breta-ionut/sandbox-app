<?php

declare(strict_types=1);

namespace App\Image\Controller;

use App\Api\Exception\ResourceNotFoundException;
use App\Core\Controller\AbstractController;
use App\Image\Model\Image;
use App\Image\Repository\ImageRepository;
use App\Image\Storage\ImageStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ImageController extends AbstractController
{
    /**
     * @param ImageRepository $imageRepository
     * @param ImageStorage    $imageStorage
     * @param string          $token
     * @param string|null     $style
     *
     * @return RedirectResponse
     *
     * @throws ResourceNotFoundException
     */
    public function getImage(
        ImageRepository $imageRepository,
        ImageStorage $imageStorage,
        string $token,
        string $style = null
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
}
