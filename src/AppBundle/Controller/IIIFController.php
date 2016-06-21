<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Imagick\Image;

class IIIFController extends Controller
{
    /**
     * {scheme}://{server}{/prefix}/{identifier}/{region}/{size}/{rotation}/{quality}.{format}.
     *
     * @see http://iiif.io/api/image/2.0/#uri-syntax
     *
     * @Route("/image/{identifier}/{region}/{size}/{rotation}/{quality}.{format}", name="_image", methods={"GET"})
     */
    public function indexAction($identifier, $region = 'full', $size, $rotation = 0, $quality = 'default', $format = 'jpg')
    {
        $hash = sha1(serialize(func_get_args()));
        $cachedFile = $this->getParameter('kernel.cache_dir').'/images/'.$hash.'.'.$format;

        $fs = new Filesystem();

        if (!$fs->exists(dirname($cachedFile))) {
            $fs->mkdir(dirname($cachedFile));
        }

        if ($fs->exists($cachedFile)) {
            return new BinaryFileResponse($cachedFile);
        }

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

        // Apply IIIF size function
        if ((trim($size) !== 'full') && (trim($size) !== 'max')) {
            $this->getSize($size, $image);
        }

        // Apply IIIF rotation function
        if (isset($rotation) && !empty($rotation)) {
            $this->getRotation($rotation, $image);
        }

        // Apply IIIF quality function
        if ((trim($quality) !== 'default') && (trim($quality) !== 'color')) {
            $this->getQuality($quality, $image);
        }
        $image->strip();

        $image->save($cachedFile, ['format' => $format]);

        return new BinaryFileResponse($cachedFile);
    }

    /*
     * Apply the requested image region as per IIIF-Image API.
     * Region parameters may be:
     *      - full
     *      - x,y,w,h
     *      - pct:x,y,w,h
     *
     * @see http://iiif.io/api/image/2.0/#region
     *
     * @param string $region  The requested image region
     * @param int $sourceImageWidth The source image width
     * @param int $sourceImageHeight The source image height
     * @param Image $image The image object
     *
     * @throws BadRequestHttpException if a region parameter missing or parameter out of image bound
     */
    protected function getRegion($region, $sourceImageWidth, $sourceImageHeight, Image $image)
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

    /*
     * Apply the requested image size as per IIIF-Image API
     * Size parameters may be:
     *      - full
     *      - w,
     *      - ,h
     *      - pct:n
     *      - w,h
     *      - !w,h
     *
     * @see http://iiif.io/api/image/2.0/#size
     *
     * @param string $size The requested image size
     * @param Image $image The image object
     *
     * @throws BadRequestHttpException if wrong size syntax given
     */
    protected function getSize($size, Image $image)
    {
        $rawSize = $size;
        if (strstr($size, '!')) {
            $size = str_replace('!', '', $size);
        }
        $regionWidth = $image->getSize()->getWidth();
        $regionHeight = $image->getSize()->getHeight();
        if (!strstr($size, 'pct')) {
            $requestedSize = explode(',', $size);
            if (count($requestedSize) != 2) {
                throw new BadRequestHttpException(sprintf('Bad Request: Size syntax %s is not valid.', $size));
            }
            $width = $requestedSize[0];
            $height = $requestedSize[1];
            if (strstr($rawSize, '!')) {
                $w = (($regionWidth / $regionHeight) * $height);
                $h = (($regionHeight / $regionWidth) * $width);
            } else {
                if (!empty($width)) {
                    $w = $width;
                } else {
                    $w = (($regionWidth / $regionHeight) * $height);
                }
                if (!empty($height)) {
                    $h = $height;
                } else {
                    $h = (($regionHeight / $regionWidth) * $width);
                }
            }
            $image->resize(new Box($w, $h));
        } elseif (strstr($size, 'pct')) {
            $requestedPercentage = explode(':', $size)[1];
            if (is_numeric($requestedPercentage)) {
                $w = (($regionWidth * $requestedPercentage) / 100);
                $h = (($regionHeight * $requestedPercentage) / 100);
                $image->resize(new Box($w, $h));
            } else {
                throw new BadRequestHttpException(sprintf('Bad Request: Size syntax %s is not valid.', $size));
            }
        }
    }

    /*
     * Apply the requested image rotation as per IIIF-Image API
     * Rotation parameters may be:
     *      - n
     *      - !n
     *
     * @see http://iiif.io/api/image/2.0/##rotation
     *
     * @param string $rotation The requested image rotation
     * @param Image $image The image object
     *
     * @throws BadRequestHttpException if wrong rotation parameters provided
     */
    protected function getRotation($rotation, Image $image)
    {
        if (isset($rotation) && !empty($rotation)) {
            $rotationDegree = str_replace('!', '', $rotation);
            if (intval($rotationDegree) <= 360) {
                if (strstr($rotation, '!')) {
                    $image->flipVertically();
                }
                $image->rotate(str_replace('!', '', $rotation));
            } else {
                throw new BadRequestHttpException(sprintf('Bad Request: Rotation argument %s is not between 0 and 360.', $rotationDegree));
            }
        }
    }

    /*
     * Apply the requested image quality as per IIIF-Image API
     *
     * Quality parameters may be:
     *      - color
     *      - gray
     *      - bitonal
     *      - default
     *
     * @see http://iiif.io/api/image/2.0/##quality
     *
     * @param string $quality The requested image quality
     * @param Image $image The image object
     *
     * @throws BadRequestHttpException if wrong quality parameters provided
     */
    protected function getQuality($quality, Image $image)
    {
        switch ($quality) {
            case 'gray':
                $image->effects()->grayscale();
                break;
            case 'bitonal':
                $max = $image->getImagick()->getQuantumRange();
                $max = $max['quantumRangeLong'];
                $imageClearnessFactor = 0.20;
                $image->getImagick()->thresholdImage($max * $imageClearnessFactor);
                break;
            default:
                throw new BadRequestHttpException(sprintf('Bad Request: %s is not a supported quality.', $quality));
        }
    }
}
