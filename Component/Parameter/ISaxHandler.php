<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

/**
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
interface ISaxHandler
{
    /**
     * Parses a given QName and returns it as array (length = 2, [0] -> "prefix" or null, [1] -> "localPart")
     * @param string $QName
     * @return array parsed QName 
     */
    public function getPrefixedName($QName);

    /**
     * Callback for the start of each element
     * @param type $parser
     * @param string $elementName element name
     * @param array $attributes attributes
     */
    public function startElement($parser, $elementName, $attributes);

    /**
     * Callback for the end of each element
     * @param type $parser parser
     * @param string $elementName element name
     */
    public function endElement($parser, $elementName);

    /**
     * Callback for the content within an element.
     * @param type $parser
     * @param string $content content
     */
    public function elementContent($parser, $content);
}