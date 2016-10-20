<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

/**
 * Description of SaxSimpleEventHandler
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class SaxSimpleEventHandler
{
    protected $handler;

    public function __construct(PostSaxParameterHandler $handler)
    {
        $this->handler = $handler;
    }

    public function onElementStart($elementName, $attributes)
    {
        $prefixed = $this->handler->getPrefixedName($elementName);
        $this->handler->xpathFromRoot($prefixed);

        foreach ($attributes as $key => $value) {
            $xpathStr = $this->handler->getXpathStr() . '/@' . $key;
            $this->handler->setRequestParameterValue($xpathStr, $value);
        }
    }

    public function onElementEnd($elementName)
    {
        $this->handler->xpathToRoot($this->handler->getPrefixedName($elementName));
    }

    public function onElementContent($content)
    {
        $xpathStr = $this->handler->getXpathStr() . '/text()';
        $this->handler->setRequestParameterValue($xpathStr, $content);
    }
}