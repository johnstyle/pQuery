<?php

namespace Johnstyle\pQuery\Component;

/**
 * Class ManipulationTrait
 *
 * @author  Jonathan SAHM <contact@johnstyle.fr>
 * @package Johnstyle\pQuery\Component
 */
trait ManipulationTrait
{
    /**
     * @param  string $attribute
     * @return string
     */
    public function attr ($attribute)
    {
        if(preg_match("#^<[^>]+" . $attribute . "=(['\"])(.*?)\\1[^>]*>#si", $this->prepareHtml(), $match)) {

            return $this->prepareText($match[2]);
        }

        return null;
    }

    /**
     * @return string
     */
    public function text ()
    {
        return $this->prepareText($this->html());
    }

    /**
     * @return string
     */
    public function html ()
    {
        if(preg_match("#^<[^>]+>(.*)</[^>]+>$#si", $this->prepareHtml(), $match)) {

            return trim($match[1]);
        }

        return null;
    }

    /**
     * @return string
     */
    private function prepareHtml()
    {
        $html = isset($this->matches[$this->index]) ? $this->matches[$this->index] : null;

        return trim(preg_replace('#(</?[a-z]+)\\\\[0-9+](\s|/?>)#si', '$1$2', $html));
    }

    /**
     * @param  string $text
     * @return string
     */
    private function prepareText ($text)
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text);

        return trim($text);
    }
}
