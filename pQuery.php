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
        if(preg_match("#^<[^>]+" . $attribute . "=['\"](.*?)['\"][^>]*>#i", $this->currentHtml, $match)) {
            return $match[1];
        }

        return false;
    }

    public function text ()
    {
        return strip_tags($this->html());
    }

    public function html ()
    {
        if(preg_match("#^<[^>]+>(.*)</[^>]+>$#i", $this->currentHtml, $match)) {
            return $match[1];
        }

        return false;
    }

    public function find ($selector, $currentHtml = false)
    {
        if(!$currentHtml) {
            $currentHtml = $this->currentHtml;
        }

        $selector = trim($selector);

        if(preg_match("#^([a-z0-9]+)(?:\[([a-z0-9\-]+)(?:(\^|!|\$|\*)?=(.+?))?\])?(?::([a-z]+)(?:\((.*?)\))?)?(?:\s(.+?))?$#i", $selector, $match)) {

            $tagName = $match[1];
            $attribute = false;
            $operator = false;
            $value = false;
            $extensionName = false;
            $extensionValue = false;
            $subSelector = false;

            if(isset($match[2])) {
                $attribute = $match[2];
            }

            if(isset($match[3])) {
                $operator = $match[3];
            }

            if(isset($match[4])) {
                $value = $match[4];
                $value = str_replace('"', '\\"', $value);
            }

            if(isset($match[5])) {
                $extensionName = $match[5];
            }

            if(isset($match[6])) {
                $extensionValue = $match[6];
            }

            if(isset($match[7])) {
                $subSelector = $match[7];
            }

            $regex = '<' . $tagName;

            if($attribute) {

                $regex .= '[^>]*\s+' . $attribute;

                if($value) {

                    switch($operator) {
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

                $regex .= '\s*=\s*[\'"]' . $value . '[\'"]';
            }

            $regex .= '(?:|\s+[^>]+|\s*/)>';
            $regex .= '.*?</' . $tagName . '>';

            if (preg_match_all("#" . $regex . "#i", $currentHtml, $matches)) {

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
                            if($this->find($subSelector, $item)) {
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
}
