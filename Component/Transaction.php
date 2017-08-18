<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;


class Transaction extends AOperation
{

    const INSERT = 'Insert';
    const UPDATE = 'Update';
    const DELETE = 'Delete';

    const ITEMS = 'items';
    const ACTION = 'action';
    const FILTER = 'filter';
    const PARAMS = 'params';

    /**
     * {@inheritdoc}
     */
    protected static $parameterMap = array(
        '/csw:Transaction/@version' => 'version',
        '/csw:Transaction/@service' => 'service',
        '/csw:Transaction/@verboseResponse' => 'verboseResponse',
        '/csw:Transaction/@requestId' => 'requestId',
        '/csw:Transaction/csw:*' => array(
            'Insert' => array(
                self::ACTION => self::INSERT,
                self::ITEMS => './gmd:MD_Metadata',
//                self::FILTER => './csw:Constraint/ogc:Filter',
                self::PARAMS => array(
                    './@handle' => 'handle',
                    './@typeName' => 'typeName',
                ),
            ),
            'Update' => array(
                self::ACTION => self::UPDATE,
                self::ITEMS => './gmd:MD_Metadata',
                self::FILTER => './csw:Constraint/ogc:Filter',
                self::PARAMS => array(
                    './@handle' => 'handle',
                ),
            ),
            'Delete' => array(
                self::ACTION => self::DELETE,
                self::ITEMS => './gmd:MD_Metadata',
                self::FILTER => './csw:Constraint/ogc:Filter',
                self::PARAMS => array(
                    './@handle' => 'handle',
                    './@typeName' => 'typeName',
                ),
            ),
        ),
    );

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var boolean
     */
    private $verboseResponse;

    private $transactions;

    /**
     * {@inheritdoc}
     */
    public function __construct(\Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $entity = null)
    {
        parent::__construct($entity);
        $this->verboseResponse = false;
        $this->transactions = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getGETParameterMap()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getPOSTParameterMap()
    {
        return self::$parameterMap;
    }

    /**
     * @param $type
     * @return bool
     * @throws CswException
     */
    public function isTypeSupported($type){
        switch ($type) {
            case self::INSERT:
                return $this->isInsertSupported();
            case self::UPDATE:
                return $this->isUpdateSupported();
            case self::DELETE:
                return $this->isDeleteSupported();
            default:
                throw new CswException($type, CswException::OperationNotSupported);
        }
    }

    /**
     * @return bool
     * @throws CswException
     */
    public function isDeleteSupported()
    {
        return $this->entity->getDelete() ? true : false;
    }

    /**
     * @return bool
     * @throws CswException
     */
    public function isInsertSupported()
    {
        return $this->entity->getInsert() ? true : false;
    }

    /**
     * @return bool
     * @throws CswException
     */
    public function isUpdateSupported()
    {
        return $this->entity->getUpdate() ? true : false;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     * @return Transaction
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVerboseResponse()
    {
        return $this->verboseResponse;
    }

    /**
     * @param bool $verboseResponse
     * @return Transaction
     */
    public function setVerboseResponse($verboseResponse)
    {
        $this->verboseResponse = $verboseResponse;

        return $this;
    }
//
//    /**
//     * @param \DOMNodeList $list
//     */
//    public function addInsert(\DOMNodeList $list){
//        $this->isInsertSupported();
//        $this->transactions[] = array(self::INSERT => $list);
//    }
//
//    /**
//     * @param \DOMNodeList $list
//     */
//    public function addUpdate(\DOMNodeList $list){
//        $this->isInsertSupported();
//        $this->transactions[] = array(self::INSERT => $list);
//    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'requestId':
                $this->setRequestId($value);
                break;
            case 'verboseResponse':
                $this->setVerboseResponse(boolval($value));
                break;
//            case self::INSERT:
//                $this->addInsert($value[self::ITEMS]);
//                break;
//            case self::UPDATE:
//                $this->addUpdate($value[self::ITEMS]);
//                break;
//            case self::DELETE:
//                $this->isDeleteSupported();
//                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function render($templating)
    {
    }
}