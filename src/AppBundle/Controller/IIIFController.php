<?php

namespace AppBundle\Controller;

use AppBundle\Model\Image;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
        $imageEntity = new Image();
        $imageEntity
            ->setIdentifier($identifier)
            ->setRegion($region)
            ->setSize($size)
            ->setRotation($rotation)
            ->setQuality($quality)
            ->setFormat($format);

        $hash = sha1(serialize(func_get_args()));
        $cachedFile = vsprintf(
            '%s/%s.%s',
            [
                $this->getParameter('image_cache'),
                $hash,
                $imageEntity->getFormat(),
            ]
        );

        $this->createCacheDirectory($cachedFile);

        $fs = new Filesystem();

        if ($fs->exists($cachedFile)) {
            return new BinaryFileResponse($cachedFile);
        }

        $imagine = $this->get('liip_imagine');
        $imageService = $this->get('image_service');

        try {
            $image = $imagine->load($this->getOriginalFileContents($identifier));
        } catch (\Exception $e) {
            $image = $imagine->load($this->getOriginalFileContents($this->getRealIdentifier($identifier)));
        }

        $imageService->getRegion($imageEntity->getRegion(), $image);
        $imageService->getSize($imageEntity->getSize(), $image);
        $imageService->getRotation($imageEntity->getRotation(), $image);
        $imageService->getQuality($imageEntity->getQuality(), $image);

        $image
            ->strip()
            ->save($cachedFile,
                ['format' => $imageEntity->getFormat()]
            );

        return new BinaryFileResponse($cachedFile);
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    protected function getRealIdentifier($identifier)
    {
        $id = explode(':', $identifier);

        if (isset($id[1])) {
            $counter = (int) $id[1] - 1;
        } else {
            $counter = 1;
        }
        $client = $this->get('solarium.client');
        $selectDocument = $client->createSelect()
            ->setQuery(sprintf('id:%s', $id[0]));

        $document = $client->select($selectDocument)->getDocuments()[0];

        $identifier = $document->nlh_id[$counter];

        return $identifier;
    }

    /**
     * @Route("/image/{identifier}/info.json", name="_iiifjson", methods={"GET"})
     */
    public function infoJsonAction($identifier)
    {
        $imageEntity = new Image();

        $imageEntity->setIdentifier($this->getRealIdentifier($identifier));

        $imagine = $this->get('liip_imagine');
        $originalImage = $this->getOriginalFileContents($this->getRealIdentifier($identifier));

        try {
            $image = $imagine->load($originalImage);
        } catch (\Exception $e) {
            throw new NotFoundHttpException(sprintf('Image with identifier %s not found',
                $imageEntity->getIdentifier()));
        }

        $ppi = $image->getImagick()->getImageResolution();
        $image->strip();

        $response = new Response(
            $this->renderView(
                'images/info.json.twig', [
                    'size' => $image->getSize(),
                    'ppi' => $ppi,
                    'identifier' => $identifier,
                ]
            )
        );

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @param string $originalIdentifier
     *
     * @return \Psr\Http\Message\StreamInterface|string
     */
    protected function getOriginalFileContents($originalIdentifier)
    {
        $id = explode(':', $originalIdentifier);
        $filename = vsprintf('/image/%s/%s/%s.jpg', [$id[0], $id[1], $id[2]]);

        $originalImageFile = $this->get('oneup_flysystem.nlh_filesystem')->read($filename);

        return $originalImageFile;
    }

    /**
     * @Route("/image/view/{identifier}", name="_iiifview", methods={"GET"})
     */
    public function viewAction($identifier)
    {
        return $this->render('images/view.html.twig', [
            'identifier' => $identifier,
        ]);
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

    /**
     * @param ConstraintViolationListInterface $errors
     */
    protected function processErrors($errors)
    {
        $errorCounter = count($errors);

        if ($errorCounter > 0) {
            $errorMessages = [];

            for ($i = 0; $i < $errorCounter; ++$i) {
                $errorMessages[] = $errors->get($i)->getMessage();
            }

            throw new BadRequestHttpException(implode('. ', $errorMessages));
        }
    }
}
