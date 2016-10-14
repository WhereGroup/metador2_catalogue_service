<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * The ContentSet represents a data set. A ContentSet object maintains a cursor pointing to its current row of data
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class ContentSet
{

    protected $operation;

    private $sum;

    private $maxValue;

    public function __construct(AOperation $operation, $maxValue = 1)
    {
        $this->operation = $operation;
        $this->sum = 0;
        $this->maxValue = $maxValue;
    }

    /**
     * Moves forward to next content item.
     * @return boolean true if next item at content exists, otherwise false
     */
    public function next()
    {
        $this->sum++;
        if($this->sum > 0 && $this->sum <= $this->maxValue){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if current position is valid;
     * @return boolean true if next item at content exists, otherwise false
     */
    public function valid()
    {
        if($this->sum > 0 && $this->sum <= $this->maxValue){
            return true;
        } else {
            return false;
        }
    }

    public function getContent() {
        if ($this->valid()) {
            return $this->operation->createResult();
        }
    }
}