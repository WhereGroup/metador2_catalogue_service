<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

/**
 * Description of SaxFilterEventHandler
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class SaxNodeEventHandler extends SaxSimpleEventHandler
{
    protected $node;
    protected $jsonStr;
    protected $current;

    public function onElementStart($elementName, $attributes)
    {
        $prefixed      = $this->handler->getPrefixedName($elementName);
        $this->handler->xpathFromRoot($prefixed);
        $this->current = array(
            'name' => $prefixed,
            'isValueSet' => false
        );

        $parameterMap = $this->handler->getParameterMap();
        if (isset($parameterMap[$this->handler->getXpathStr()])) { # xpath to an element
            $this->node    = $this->handler->getXpathStr();
            $this->jsonStr = '{"children":[';
        } elseif ($this->node) {
            $this->jsonStr .= '{"' . $prefixed[1] . '":{';
            foreach ($attributes as $key => $value) {
                $this->jsonStr .= '"' . $key . '":"' . addslashes($value) . '",';
            }
            $this->jsonStr .= '"children":[';
        } else { # xpath to a value
            foreach ($attributes as $key => $value) {
                $xpathStr = $this->handler->getXpathStr() . '/@' . $key;
                $this->handler->setParameter($xpathStr, $value);
            }
        }
    }

    public function onElementEnd($elementName)
    {
        if ($this->node === $this->handler->getXpathStr()) {
            $this->jsonStr = preg_replace('/,$/', '', $this->jsonStr) . ']}';
            $node          = json_decode($this->jsonStr, true);
            $this->handler->setParameter($this->node, $node['children']);
            $this->node    = null;
        } elseif ($this->node) {
            $test = '"children":[';
            $testPos = strlen($this->jsonStr) - strlen($test);
            if (strrpos($this->jsonStr, $test) === $testPos) {
                $this->jsonStr = substr($this->jsonStr, 0, $testPos);
                $this->jsonStr = preg_replace('/,$/', '', $this->jsonStr) . '}},';
            } else {
                $this->jsonStr = preg_replace('/,$/', '', $this->jsonStr) . ']}},';
            }
        }
        $this->handler->xpathToRoot($this->handler->getPrefixedName($elementName));
    }

    public function onElementContent($content)
    {
        if (!$this->node) {
            $xpathStr = $this->handler->getXpathStr() . '/text()';
            $this->handler->setParameter($xpathStr, $content);
        } else {#if (!$this->current['isValueSet']) { # read only first time
//            $string = addslashes(preg_replace(array('/\s+$/', '/^\s+/'), array('', ''), $content));
            if(($string = addslashes(preg_replace(array('/\s+$/', '/^\s+/'), array('', ''), $content))) !== '') {
                $test = '"children":[';
                $testPos = strlen($this->jsonStr) - strlen($test);
                if (strrpos($this->jsonStr, $test) === $testPos) {
                    $this->jsonStr = substr($this->jsonStr, 0, $testPos);
                    $this->jsonStr .= '"VALUE":"' . $string . '","children":[';
                }
//                else {
//                    $this->jsonStr = preg_replace('/,$/', '', $this->jsonStr) . ']}},';
//                }
//
//
//
//                $this->jsonStr .= $string === '' ? '' : '"VALUE":"' . $string . '",';
            }
//            $this->jsonStr .= $string === '' ? '' : '"VALUE":"' . $string . '",';
////            $this->jsonStr .= '"children":[';
//            $this->current['isValueSet'] = true;
        }
    }
}