<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.17
 * Time: 14:21
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Transaction;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\TransactionAction;

class TransactionParameterHandler extends PostDomParameterHandler
{
    private $typeIdx = -1;

    /**
     * {@inheritdoc}
     */
    public function initOperation(AOperation $operation)
    {
        $parameterMap = $operation->getPOSTParameterMap();
        $parameters = array();
        foreach ($parameterMap as $key => $value) {
            if (is_string($value)) {
                $parameters[$value] = $this->getParameter($key);
            }
        }
        $operation->setParameters($parameters);
        $this->reset();
    }

    /**
     * @param $xpath
     * @param null|\DOMElement $contextElm
     * @param bool $allowSingle
     * @return mixed
     */
    public function valueFor($xpath, $contextElm = null, $allowSingle = true)
    {
        return $this->getValue($xpath, $contextElm, $allowSingle);
    }

    /**
     *
     */
    public function reset()
    {
        $this->typeIdx = -1;
    }

    /**
     * @param Transaction $operation
     * @return array
     */
    private function getTypeConfiguration(Transaction $operation)
    {
        $parameterMap = $operation->getPOSTParameterMap();
        foreach ($parameterMap as $key => $value) {
            if (!is_string($value)) {
                return array('key' => $key, 'value' => $value);
            }
        }
    }

    /**
     * @param Transaction $operation
     * @return null|TransactionAction
     */
    public function nextAction(Transaction $operation)
    {
        $conf = $this->getTypeConfiguration($operation);
        $xpathStr = $conf['key'];
        $typesConf = $conf['value'];
        $list = $this->getValue($xpathStr, $this->dom->documentElement, false);
        $this->typeIdx++;
        if ($this->typeIdx < count($list)) {
            /**
             * @var \DOMElement
             */
            $actionNode = $list[$this->typeIdx];
            $config = $typesConf[$actionNode->localName];

            return $this->initAction($operation, $actionNode, $config);
        } else {
            return null;
        }
    }

    /**
     * @param Transaction $operation
     * @param \DOMElement $actionElm
     * @param array $config
     * @return TransactionAction
     * @throws CswException
     */
    private function initAction(Transaction $operation, \DOMElement $actionElm, array $config)
    {
        if (!$operation->isTypeSupported($config[Transaction::ACTION])) {
            throw new CswException($config[Transaction::ACTION], CswException::OperationNotSupported);
        }
        $action = new TransactionAction(
            $config[Transaction::ACTION],
            isset($config[Transaction::FILTER]) ? $config[Transaction::FILTER] : null
        );
        foreach ($config[Transaction::PARAMS] as $key => $value) {
            $action->setParameter($value, $this->getParameter($key, $actionElm));
        }
        $action->setItems($this->getValue($config[Transaction::ITEMS], $actionElm, false));

        return $action;
    }
}