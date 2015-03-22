<?php

namespace Johnstyle\pQuery;

use Johnstyle\pQuery\Component\ManipulationTrait;
use Johnstyle\pQuery\Component\TraversingTrait;

/**
 * Class pQuery
 *
 * @author  Jonathan SAHM <contact@johnstyle.fr>
 * @package Johnstyle\pQuery
 */
class pQuery
{
    use TraversingTrait;
    use ManipulationTrait;

    const DEFAULT_INPUT_CHARSET = 'UTF-8';
    const DEFAULT_OUTPUT_CHARSET = 'UTF-8';

    /** @var string $charsetInput */
    private $charsetInput = self::DEFAULT_INPUT_CHARSET;

    /** @var string $charsetOutput */
    private $charsetOutput = self::DEFAULT_OUTPUT_CHARSET;

    /** @var string $html */
    private $html;

    /** @var array $matches */
    public $matches = array();

    /** @var int $index */
    private $index = 0;

    /**
     * @param string $html
     * @param string $charsetInput
     * @param string $charsetOutput
     */
    public function __construct ($html, $charsetInput = self::DEFAULT_OUTPUT_CHARSET, $charsetOutput = self::DEFAULT_OUTPUT_CHARSET)
    {
        $this->html = $html;
        $this->charsetInput = $charsetInput;
        $this->charsetOutput = $charsetOutput;

        $this->initHtml();
    }

    /**
     * @param  string $selector
     * @return static
     */
    public function find ($selector)
    {
        return (new static($this->html))->parse($selector);
    }

    /**
     * @param  string $selector
     * @param  string $html
     * @return $this
     */
    private function parse ($selector, $html = null, $level = 0)
    {
        if(is_null($html)) {

            $this->matches = array();
            $html = $this->html;
        }

        $selector = trim($selector);

        if(preg_match("#^(>\s*)?([a-z0-9]+)(?:\[([a-z0-9\-]+)(?:(\^|!|\$|\*)?=(.+?))?\]|((\#|\.)([a-z0-9\-]+)))?(?::([a-z]+)(?:\((.*?)\))?)?(?:\s(.+?))?$#si", $selector, $match)) {

            $firstLevel = $match[1];
            $tagName = $match[2];
            $attribute = false;
            $operator = false;
            $value = false;
            $extensionName = false;
            $extensionValue = false;
            $subSelector = false;

            if(isset($match[3]) && $match[3]) {

                $attribute = $match[3];
            }

            if(isset($match[4]) && $match[4]) {

                $operator = $match[4];

            } elseif(isset($match[7]) && $match[7]) {

                $operator = $match[7];

                switch($operator) {

                    case '.':

                        $attribute = 'class';
                        break;

                    case '#':

                        $attribute = 'id';
                        break;
                }
            }

            if(isset($match[5]) && $match[5]) {

                $value = $match[5];
                $value = str_replace('"', '\\"', $value);

            } elseif(isset($match[8]) && $match[8]) {

                $value = $match[8];
            }

            if(isset($match[9]) && $match[9]) {

                $extensionName = $match[9];
            }

            if(isset($match[10]) && $match[10]) {

                $extensionValue = $match[10];
            }

            if(isset($match[11]) && $match[11]) {

                $subSelector = trim($match[11]);
            }

            $regex = '<' . $tagName . '(\\\\(' . ($firstLevel ? $level : '[0-9]+') . ')\-([0-9]+))';

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

                $regex .= '\s*=\s*([\'"])' . $value . '\4';

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

                        $this->parse($subSelector, $matches[5][$i], $matches[2][$i]+1);
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
     * @return $this
     */
    private function initHtml()
    {
        $counter = array();
        $level = 0;

        if($this->charsetInput !== $this->charsetOutput) {

            $this->html = mb_convert_encoding($this->html, $this->charsetOutput, $this->charsetInput);
        }

        $this->html = preg_replace_callback("#(?:<([a-z]+)(\s[^>]*?|)(/)?>|</([a-z]+)>)#si", function ($match) use(&$level, &$counter) {

            if(isset($match[4])) {

                $tagname = $match[4];

                $counter[$tagname]--;

                $level--;

                $tagName = '</' . $tagname . '\\' . $level . '-' . $counter[$tagname] . '>';

            } else {

                $tagname = $match[1];
                $attributes = $match[2];
                $slash = isset($match[3]) ? $match[3] : null;

                if(!isset($counter[$tagname])) {

                    $counter[$tagname] = 0;
                }

                $tagName = '<' . $tagname . '\\' . $level . '-' . $counter[$tagname] . $attributes . $slash . '>';

                if(is_null($slash)
                    && !in_array($tagname, array('img', 'link', 'meta', 'input', 'br', 'hr', 'area', 'base',
                        'basefont', 'col', 'embed', 'param', 'frame', 'keygen', 'source', 'track'))) {

                    $level++;
                    $counter[$match[1]]++;
                }
            }

            return $tagName;

        }, $this->html);

        return $this;
    }
}
