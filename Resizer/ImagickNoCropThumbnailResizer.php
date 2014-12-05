<?php

namespace Ibrows\SonataMediaResizerBundle\Resizer;

use Gaufrette\File;
use Imagine\Image\Box;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;

class ImagickNoCropThumbnailResizer implements ResizerInterface
{
    /**
     * @var MetadataBuilderInterface
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @param MetadataBuilderInterface $metadata
     */
    public function __construct(MetadataBuilderInterface $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @param MediaInterface $media
     * @param File           $in
     * @param File           $out
     * @param string         $format
     * @param array          $settings
     *
     * @return void
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings)
    {
        if(!extension_loaded('imagick')){
            $out->setContent($in->getContent(), $this->metadata->get($media, $out->getName()));
            return;
        }

        $this->validateSettings($media, $settings);

        list($width, $height) = $this->getDimensions($media, $settings);

        $image = new \Imagick();
        $image->newImage($settings['width'], $settings['height'], new \ImagickPixel('none'));
        $image->setImageFormat('png');

        $origImage = new \Imagick();
        $origImage->readimageblob($in->getContent());
        $origImage->resizeImage($width, $height, \Imagick::FILTER_LANCZOS,1);

        $image->compositeImage($origImage, \Imagick::COMPOSITE_OVER, ($settings['width'] - $width) / 2 , ($settings['height'] - $height) / 2);

        $out->setContent($image, $this->metadata->get($media, $out->getName()));
    }

    /**
     * @param MediaInterface $media
     * @param array $settings
     * @return Box
     */
    public function getBox(MediaInterface $media, array $settings)
    {
        return new Box($settings['width'], $settings['height']);
    }

    /**
     * @param MediaInterface $media
     * @param array $settings
     * @return array
     * @throws \RuntimeException
     */
    protected function getDimensions(MediaInterface $media, array $settings)
    {
        $this->validateSettings($media, $settings);

        $origWidth = $media->getWidth();
        $origHeight = $media->getHeight();
        $origRatio = $origWidth / $origHeight;

        $wishedWidth = $settings['width'];
        $wishedHeight = $settings['height'];
        $wishedRatio = $wishedWidth / $wishedHeight;

        // orig image is wider than the wished
        if($origRatio > $wishedRatio) {
            if($origWidth > $wishedWidth) {
                $width = $wishedWidth;
            } else {
                $width = $origWidth;
            }
            $height = $width / $origRatio;
        } else {
            if($origHeight > $wishedHeight) {
                $height = $wishedHeight;
            } else {
                $height = $origHeight;
            }
            $width = $origRatio * $height;
        }

        return array($width, $height);
    }

    /**
     * @param MediaInterface $media
     * @param array          $settings
     */
    protected function validateSettings(MediaInterface $media, array &$settings)
    {
        if (!$settings['width'] && !$settings['height']) {
            throw new \RuntimeException(sprintf('Width and height parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderName()));
        }

        if (!$settings['width']) {
            $settings['width'] = $settings['height'];
        }

        if (!$settings['height']) {
            $settings['height'] = $settings['width'];
        }
    }
}