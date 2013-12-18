<?php

class pQuery
{
    private $html;
    private $currentHtml;

    public function __construct ($html)
    {
        $this->html = $html;
        $this->currentHtml = $html;
    }

    public function attr ($attribute = false)
    {
        if(preg_match("#^<[^>]+" . $attribute . "=(['\"])(.*?)\\1[^>]*>#si", $this->currentHtml, $match)) {
            return $match[2];
        }

        return false;
    }

    public function text ()
    {
        return strip_tags($this->html());
    }

    public function html ()
    {
        if(preg_match("#^<[^>]+>(.*)</[^>]+>$#si", $this->currentHtml, $match)) {
            return $match[1];
        }

        return false;
    }

    public function find ($selector, $currentHtml = false)
    {
        if(!$currentHtml) {

            $this->setUnic();

            $currentHtml = $this->currentHtml;
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

            if (preg_match_all("#" . $regex . "#si", $currentHtml, $matches)) {

                foreach($matches[0] as $i => $item) {

                    $continue = false;
                    switch($extensionName) {
                       default:
                            $continue = true;
                            break;
                        case 'eq':
                            if($i == $extensionValue) {
                                $continue = true;
                            }
                            break;
                        case 'first':
                            if($i == 0) {
                                $continue = true;
                            }
                            break;
                        case 'last':
                            if($i == (count($matches[0]) - 1)) {
                                $continue = true;
                            }
                            break;

                    }

                    if($continue) {
                        if($subSelector) {
                            if($this->find($subSelector, $matches[3][$i])) {
                                break;
                            }
                        } else {
                            $this->currentHtml = $item;
                            break;
                        }
                    }
                }

                return $this;
            }
        }

        return false;
    }

    private function setUnic()
    {
        $counter = array();

        $this->currentHtml = preg_replace_callback("#((<)([a-z]+)(\s[^>]*|)(/?>)|(</)([a-z]+)(>))#si", function ($match) use(&$counter) {

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
            }, $this->currentHtml);
    }
}
