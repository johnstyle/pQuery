<?php

namespace Johnstyle\pQuery;

/**
 * Class pQuery
 *
 * @author  Jonathan SAHM <contact@johnstyle.fr>
 * @package Johnstyle\pQuery
 */
class pQuery
{
    /** @var string $html */
    private $html;

    /** @var array $matches */
    private $matches = array();

    /** @var int $index */
    private $index = 0;

    /**
     * @param string $html
     */
    public function __construct ($html)
    {
        $this->setHtml($html);
    }

    /**
     * @param  string $attribute
     * @return string
     */
    public function attr ($attribute)
    {
        if(preg_match("#^<[^>]+" . $attribute . "=(['\"])(.*?)\\1[^>]*>#si", $this->prepareHtml(), $match)) {

            return trim($match[2]);
        }

        return null;
    }

    /**
     * @return string
     */
    public function text ()
    {
        return trim(strip_tags($this->html()));
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
     * @param  int $index
     * @return $this
     */
    public function eq ($index)
    {
        $this->index = (int) $index;

        return $this;
    }

    /**
     * @param  string $selector
     * @param  string $html
     * @return $this
     */
    public function find ($selector, $html = null)
    {
        if(is_null($html)) {

            $this->matches = array();
            $html = $this->html;
        }

        $selector = trim($selector);

        if(preg_match("#^([a-z0-9]+)(?:\[([a-z0-9\-]+)(?:(\^|!|\$|\*)?=(.+?))?\]|((\#|\.)([a-z0-9\-]+)))?(?::([a-z]+)(?:\((.*?)\))?)?(?:\s(.+?))?$#si", $selector, $match)) {

            $tagName = $match[1];
            $attribute = false;
            $operator = false;
            $value = false;
            $extensionName = false;
            $extensionValue = false;
            $subSelector = false;

            if(isset($match[2]) && $match[2]) {

                $attribute = $match[2];
            }

            if(isset($match[3]) && $match[3]) {

                $operator = $match[3];

            } elseif(isset($match[6]) && $match[6]) {

                $operator = $match[6];

                switch($operator) {

                    case '.':

                        $attribute = 'class';
                        break;

                    case '#':

                        $attribute = 'id';
                        break;
                }
            }

            if(isset($match[4]) && $match[4]) {

                $value = $match[4];
                $value = str_replace('"', '\\"', $value);

            } elseif(isset($match[7]) && $match[7]) {

                $value = $match[7];
            }

            if(isset($match[8]) && $match[8]) {

                $extensionName = $match[8];
            }

            if(isset($match[9]) && $match[9]) {

                $extensionValue = $match[9];
            }

            if(isset($match[10]) && $match[10]) {

                $subSelector = $match[10];
            }

            $regex = '<' . $tagName . '(\\\\[0-9]+)';

            if($attribute) {

                $regex .= '[^>]*\s+' . $attribute;

                if($value) {

                    switch($operator) {

                        case '.':

                            $value = '(?:|.*\s)' . $value . '(?:|\s.*)';
                            break;

                        case '^':

                            $value = $value . '.+?';
                            break;

                        case '$':

                            $value = '.+?' . $value;
                            break;

                        case '*':

                            $value = '.*?' . $value . '.*?';
                            break;

                        case '!':

                            //$value = '(?:(?<!' . $value . ').+?|.+?(?!' . $value . '))';
                            $value = '(?!' . $value . ')';
                            break;
                    }

                } else {

                    $value = '.*?';
                }

                $regex .= '\s*=\s*([\'"])' . $value . '\2';

            } else {

                $regex .= '()';
            }

            $regex .= '(?:|\s+[^>]+|\s*/)>';
            $regex .= '(.*?)</' . $tagName . '\1>';

            if (preg_match_all("#" . $regex . "#si", $html, $matches)) {

                foreach($matches[0] as $i => $item) {

                    switch($extensionName) {

                        case 'eq':

                            if($i !== (int) $extensionValue) {

                                continue(2);
                            }
                            break;

                        case 'first':

                            if($i !== 0) {

                                break(2);
                            }
                            break;

                        case 'last':

                            if($i !== (count($matches[0]) - 1)) {

                                continue(2);
                            }
                            break;
                    }

                    if(false !== $subSelector) {

                        $this->find($subSelector, $matches[3][$i]);
                        break;
                    }

                    array_push($this->matches, $item);
                }

            } else {

                $this->html = null;
            }
        }

        return $this;
    }

    /**
     * @param  callable $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        $matches = $this->matches;

        foreach($matches as $match) {

            $this->html = $match;

            call_user_func_array($callback, array(&$this));
        }

        return $this;
    }

    /**
     * @param  string $html
     * @return $this
     */
    private function setHtml($html)
    {
        $counter = array();

        $this->html = preg_replace_callback("#((<)([a-z]+)(\s[^>]*|)(/?>)|(</)([a-z]+)(>))#si", function ($match) use(&$counter) {

            if(isset($match[7])) {

                $counter[$match[7]]--;

                $tagName = $match[6] . $match[7] . '\\' . $counter[$match[7]] . $match[8];

            } else {

                if(!isset($counter[$match[3]])) {

                    $counter[$match[3]] = 0;
                }

                $tagName = $match[2] . $match[3] . '\\' . $counter[$match[3]] . $match[4] . $match[5];

                $counter[$match[3]]++;
            }

            return $tagName;

        }, $html);

        return $this;
    }

    /**
     * @return string
     */
    private function prepareHtml()
    {
        $html = isset($this->matches[$this->index]) ? $this->matches[$this->index] : null;

        return trim(preg_replace('#(</?[a-z]+)\\\\[0-9+](\s|/?>)#si', '$1$2', $html));
    }
}
