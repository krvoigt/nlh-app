<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IIIFController extends Controller
{
    /**
     * {scheme}://{server}{/prefix}/{identifier}/{region}/{size}/{rotation}/{quality}.{format}.
     *
     * @see http://iiif.io/api/image/2.0/#uri-syntax
     *
     * @Route("/image/{identifier}/{region}/{size}/{rotation}/{quality}.{format}", name="_image", methods={"GET"})
     */
    public function indexAction($identifier, $region, $size, $rotation, $quality, $format)
    {
        $imageEntity = new \AppBundle\Entity\Image();
        $imageEntity
            ->setIdentifier($identifier)
            ->setRegion($region)
            ->setSize($size)
            ->setRotation($rotation)
            ->setQuality($quality)
            ->setFormat($format);

        $errors = $this->get('validator')->validate($imageEntity);

        if (count($errors) > 0) {
            $errorMessages = [];

            for ($i = 0; $i < count($errorMessages); ++$i) {
                $errorMessages[] = $errors->get($i)->getMessage();
            }

            throw new BadRequestHttpException(implode('. ', $errorMessages));
        }

        $hash = sha1(serialize(func_get_args()));
        $cachedFile = vsprintf(
            '%s/images/%s.%s',
            [
                $this->getParameter('kernel.cache_dir'),
                $hash,
                $imageEntity->getFormat(),
            ]
        );

        $this->createCacheDirectory($cachedFile);

        $fs = new Filesystem();

        if ($fs->exists($cachedFile)) {
            return new BinaryFileResponse($cachedFile);
        }

        $client = $this->get('guzzle.client.tiff');
        $imagine = $this->get('liip_imagine');
        $imageService = $this->get('image_service');

        $originalImage = $client->get($imageEntity->getIdentifier().'.tif');

        try {
            $image = $imagine->load($originalImage->getBody());
        } catch (\Exception $e) {
            throw new NotFoundHttpException(sprintf('Image with identifier %s not found', $imageEntity->getIdentifier()));
        }

        $imageService
            ->getRegion($imageEntity->getRegion(), $image)
            ->getSize($imageEntity->getSize(), $image)
            ->getRotation($imageEntity->getRotation(), $image)
            ->getQuality($imageEntity->getQuality(), $image);

        $image
            ->strip()
            ->save($cachedFile,
                ['format' => $imageEntity->getFormat()]
            );

        return new BinaryFileResponse($cachedFile);
    }

    /**
     * @param string $file
     */
    protected function createCacheDirectory($file)
    {
        $fs = new Filesystem();

        if (!$fs->exists(dirname($file))) {
            $fs->mkdir(dirname($file));
        }
    }
}
