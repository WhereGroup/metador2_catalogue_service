<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.17
 * Time: 14:21
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Operation;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Transaction;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\TransactionOperation;
use WhereGroup\CoreBundle\Component\Search\ExprHandler;

class TransactionParameter extends PostDomParameter
{
    private $typeIdx = -1;

    /**
     * {@inheritdoc}
     */
    public function initOperation(Operation $operation)
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

        return $operation;
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
     * @param ExprHandler $expression
     * @return null|Transaction|TransactionOperation
     * @throws CswException
     * @throws \Exception
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function nextAction(Transaction $operation, ExprHandler $expression)
    {
        $conf = $this->getTypeConfiguration($operation);
        $xpathStr = $conf['key'];
        $typesConf = $conf['value'];
        $list = $this->getValue($xpathStr, $this->dom->documentElement, false);
        $this->typeIdx++;
        if ($this->typeIdx < count($list)) {
            /* @var \DOMElement $actionNode */
            $actionNode = $list[$this->typeIdx];
            $config = $typesConf[$actionNode->localName];

            return $this->initAction($operation, $expression, $actionNode, $config);
        } else {
            return null;
        }
    }

    /**
     * @param Transaction $transaction
     * @param ExprHandler $exprHandler
     * @param \DOMElement $actionElm
     * @param array $config
     * @return TransactionOperation
     * @throws CswException
     * @throws \Exception
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    private function initAction(
        Transaction $transaction,
        ExprHandler $exprHandler,
        \DOMElement $actionElm,
        array $config
    ) {
        if (!$transaction->isTypeSupported($config[Transaction::ACTION])) {
            throw new CswException($config[Transaction::ACTION], CswException::OPERATIONNOTSUPPORTED);
        }
        $action = new TransactionOperation($config[Transaction::ACTION], $exprHandler);
        foreach ($config[Transaction::PARAMS] as $key => $value) {
            $action->setParameter($value, $this->getParameter($key, $actionElm));
        }
        if (isset($config[Transaction::FILTER])) {
            if (($filter = $this->getValue($config[Transaction::FILTER], $actionElm, true)) === null) {
                throw new CswException(Transaction::FILTER, CswException::MISSINGPARAMETERVALUE);
            }
            $action->setConstraint($filter);
        }
        try {
            $action->setItems($this->getValue($config[Transaction::ITEMS], $actionElm, false));
        } catch (\Exception $ex) {
            ;
        }

        return $action;
    }
}
