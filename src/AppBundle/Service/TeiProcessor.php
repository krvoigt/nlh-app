<?php

namespace AppBundle\Service;

/**
 * Processes TEI files.
 */
class TeiProcessor
{
    public function process($content)
    {
        $content = trim(strip_tags($content, '<lb>'));
        $content = str_replace('<lb/>', '<br/>', $content);

        return $content;
    }
}
