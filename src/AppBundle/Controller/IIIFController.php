<?php

namespace AppBundle\Controller;

use Imagine\Image\ImageInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IIIFController extends Controller
{
    /**
     * {scheme}://{server}{/prefix}/{identifier}/{region}/{size}/{rotation}/{quality}.{format}.
     *
     * @see http://iiif.io/api/image/2.0/#uri-syntax
     *
     * @Route("/image/{identifier}/{region}/{size}/{rotation}/{quality}.{format}", methods={"GET"})
     */
    public function indexAction($identifier, $region, $size, $rotation, $quality, $format)
    {
        $client = $this->get('guzzle.client.tiff');
        $imagine = $this->get('liip_imagine');

        if (strpos($identifier, ':')) {
            $sourceFileIdentifier = str_replace(':', '/', $identifier);
        } else {
            throw new NotFoundHttpException(sprintf('Invalid identifier: %s', $identifier));
        }

        $originalImage = $client->get($sourceFileIdentifier.'.tif');

        try {
            $image = $imagine->load($originalImage->getBody());
        } catch (\Exception $e) {
            throw new NotFoundHttpException(sprintf('Image with identifier %s not found', $identifier));
        }

        $imageOriginalSize = $image->getSize();
        $sourceImageWidth = $imageOriginalSize->getWidth();
        $sourceImageHeight = $imageOriginalSize->getHeight();

        // Apply IIIF region function
        if (isset($region) && $region !== 'full') {
            $this->getRegion(trim($region), $sourceImageWidth, $sourceImageHeight, $image);
        }

        return new BinaryFileResponse($image->show($format));
    }

    /*
     * Apply the requested image region as per IIIF-Image API.
     *
     * @param string $region  The requested image region
     * @param int $sourceImageWidth The source image width
     * @param int $sourceImageHeight The source image height
     * @param ImageInterface $image The image object
     *
     * @throws BadRequestHttpException if a region parameter missing or parameter out of image bound
     */
    protected function getRegion($region, $sourceImageWidth, $sourceImageHeight, ImageInterface $image)
    {
        if ($region === 'square') {
            $regionSort = 'squareBased';
        } elseif (strstr($region, 'pct')) {
            $regionSort = 'percentageBased';
        } else {
            $regionSort = 'pixelBased';
        }

        switch ($regionSort) {
            case 'squareBased':
                $calculateShorterDimension = $sourceImageWidth < $sourceImageHeight ? $sourceImageWidth : $sourceImageHeight;
                $calculateLongerDimension = $sourceImageWidth < $sourceImageHeight ? $sourceImageHeight : $sourceImageWidth;
                $imageLeftRightMargin = (($calculateLongerDimension - $calculateShorterDimension) / 2);
                $x = 0;
                $y = $imageLeftRightMargin;
                $w = $calculateShorterDimension;
                $h = $calculateShorterDimension;
                break;
            case 'pixelBased':
                $imageCoordinates = explode(',', $region);
                if (count($imageCoordinates) < 4) {
                    throw new BadRequestHttpException('Bad Request: Exactly (4) coordinates must be supplied.');
                }
                $x = $imageCoordinates[0];
                $y = $imageCoordinates[1];
                $w = $imageCoordinates[2];
                $h = $imageCoordinates[3];
                break;
            case 'percentageBased':
                $imageCoordinates = explode(',', explode(':', $region)[1]);
                if (count($imageCoordinates) < 4) {
                    throw new BadRequestHttpException('Bad Request: Exactly (4) coordinates must be supplied.');
                }
                if ((isset($imageCoordinates[0]) && $imageCoordinates[0] >= 100) ||
                        (isset($imageCoordinates[1]) && $imageCoordinates[1] >= 100)) {
                    throw new BadRequestHttpException('Bad Request: Crop coordinates are out of bound.');
                }
                $x = ceil(($imageCoordinates[0] / 100) * $sourceImageWidth);
                $y = ceil(($imageCoordinates[1] / 100) * $sourceImageHeight);
                $w = ceil(($imageCoordinates[2] / 100) * $sourceImageWidth);
                $h = ceil(($imageCoordinates[3] / 100) * $sourceImageHeight);
                break;
            default:
                $x = 0;
                $y = 0;
                $w = $sourceImageWidth;
                $h = $sourceImageHeight;
        }

        $image->crop(new Point($x, $y), new Box($w, $h));
    }
}
