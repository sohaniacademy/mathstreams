<?php

/*
 *  This file is part of Mathstreams.
 *
 *  Copyright (c) 2015  Sohani Academy <developer@sohaniacademy.com>
 *
 *  Mathstreams is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Magnets\MathgymBundle\Service;

class InputValidationService
{

    protected $purifier;

    public function __construct(\HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
        /*
          $config = \HTMLPurifier_Config::createDefault();
          $config->set('HTML.DefinitionID', 'input_validation');
          $config->set('HTML.DefinitionRev', 1);
          if ($def = $config->maybeGetRawHTMLDefinition()) {
          $def->addAttribute('p', 'opt', 'Number');

          //add support for question tags.. 'q' = something etc
          $qTags = $def->addElement(
          'q', // name
          'Block', // content set
          'Flow', // allowed children
          'Common', // attribute collection
          array(// attributes
          'action*' => 'URI',
          'method' => 'Enum#get|post',
          'name' => 'ID'
          )
          );
          }
          $this->purifier = new HTMLPurifier($config);
         */
    }

    public function cleanupJSON($ob)
    {
        $allowedBlockTypes = ['h1', 'h2', 'h3', 'h4', 'p', 'ph', 'img', 'q', 'ggb'];
        $cleanBlocks = [];
        foreach (json_decode($ob, true) as $block) {
            if (in_array($block['type'], $allowedBlockTypes)){
                $cleanBlocks [] = ['type' => $block['type'], 'val' => $this->cleanup($block['val'])];
            }
        }
        return json_encode($cleanBlocks);
    }

    public function stripTag($doc, $tag)
    {

        $list = $doc->getElementsByTagName($tag);

        while ($list->length > 0) {
            $el = $list->item(0);
            $el->parentNode->removeChild($el);
        }
        return $doc;
    }

    public function cleanup($text)
    {
        return $this->purifier->purify($text);
        /*

          $doc = new DOMDocument();
          $doc->loadHTML($text);

          //remove all script and iframe tags:
          $doc = stripTag($doc, "script");
          $doc = stripTag($doc, "iframe");
          return $doc->saveHTML();
         */
    }

    public function cleanupArr($arr)
    {
        foreach ($arr as $key => $value) {
            $arr[$key] = $this->cleanup($value);
        }
        return $arr;
    }

}
