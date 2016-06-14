<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Imagine\Image\Box;
use Imagine\Image\Point;

class IIIFController extends Controller
{
    /**
     * @Route("/iiif/{identifier}/{region}/{size}/{rotation}/{quality}.{format}")
     */
    public function indexAction($identifier, $region, $size, $rotation, $quality, $format)
    {
        $sourceFileBaseUri = 'http://gdz.sub.uni-goettingen.de/tiff/';
        $sourceFileIdentifier = str_replace(':', '/', $identifier);
        $sourceFileFormat = 'tif';
        $sourceFile = $sourceFileBaseUri.$sourceFileIdentifier.'.'.$sourceFileFormat;

        $imagine = $this->get('liip_imagine');
        $image = $imagine->open($sourceFile);
        $imageOriginalSize = $image->getSize();
        $imageWidth = $imageOriginalSize->getWidth();
        $imageHeight = $imageOriginalSize->getHeight();

        if (isset($region) && $region !== 'full') {
            list($x, $y, $w, $h) = $this->getImageCoordinates($region, $imageWidth, $imageHeight);
            $image->crop(new Point($x, $y), new Box($w, $h));
        }

        if (isset($rotation) && !empty($rotation)) {
            $image->rotate(str_replace('!', '', $rotation));
            if (strstr($rotation, '!')) {
                $image->flipHorizontally();
            }
        }

        return new BinaryFileResponse($image->show($format));
    }

    /*
     * Returns the coordinates of the requested image region.
     *
     * @param string $region  The required image region to be returned
     * @param int $imageWidth The image width
     * @param int $imageHeight The image height
     *
     * @return Array An array consisting of coordinates of the requeseted image region
     *
     * @throws BadRequestHttpException if a region parameter missing or parameter out of image bound
     */
    protected function getImageCoordinates($region, $imageWidth, $imageHeight)
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
                $calculateShorterDimension = $imageWidth < $imageHeight ? $imageWidth : $imageHeight;
                $calculateLongerDimension = $imageWidth < $imageHeight ? $imageHeight : $imageWidth;
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
                $x = ceil(($imageCoordinates[0] / 100) * $imageWidth);
                $y = ceil(($imageCoordinates[1] / 100) * $imageHeight);
                $w = ceil(($imageCoordinates[2] / 100) * $imageWidth);
                $h = ceil(($imageCoordinates[3] / 100) * $imageHeight);
                break;
        }

        return [$x, $y, $w, $h];
    }
}
