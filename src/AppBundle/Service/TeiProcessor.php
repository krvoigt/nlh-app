<?php

namespace AppBundle\Service;

/**
 * Processes TEI files.
 */
class TeiProcessor
{
    public function process($content)
    {
        $content = trim(strip_tags($content));

        return $content;
    }
}
