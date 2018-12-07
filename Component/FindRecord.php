<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use WhereGroup\CoreBundle\Component\Search\ExprHandler;

/**
 * Description of Operation
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
abstract class FindRecord extends Operation
{
    const QUERYABLE_MAP = array(
        'Identifier' => 'uuid',
        'Title' => 'title',
        'Abstract' => 'abstract',
    );
    const RESULTTYPE_HITS = 'hits';
    const RESULTTYPE_RESULTS = 'results';
    const RESULTTYPE_VALIDATE = 'validate';

    const ELEMENTSET_BRIEF = 'brief';
    const ELEMENTSET_SUMMARY = 'summary';
    const ELEMENTSET_FULL = 'full';

    /* supported element sets */
    const ELEMENTSET = array('brief', 'summary', 'full');
    const RESULTTYPE = array('hits', 'results', 'validate');

    /**
     * @var string
     */
    protected $outputSchema;

    /**
     * @var string
     */
    protected $elementSetName;

    /*  @var ExprHandler $exprHandler */
    protected $exprHandler;
    /* @var Expression $constraint */
    protected $constraint;

    public function __construct(
        \Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $entity = null,
        ExprHandler $exprHandler
    ) {
        parent::__construct($entity);
        $this->exprHandler = $exprHandler;
        $this->outputSchema = "http://www.isotc211.org/2005/gmd";
        $this->elementSetName = 'summary';
    }

    /**
     * @return Expression
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * @param mixed $constraintContent
     * @return $this
     * @throws CswException
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    abstract public function setConstraint($constraintContent);


    /**
     * Returns the output schema.
     * @return string output schema
     */
    public function getOutputSchema()
    {
        return $this->outputSchema;
    }

    /**
     * @param $outputSchema
     * @return $this
     * @throws CswException
     */
    public function setOutputSchema($outputSchema)
    {
        // use function to check only
        self::isStringAtList('outputSchema', $outputSchema, array($this->outputSchema), false);

        return $this;
    }

    /**
     * @return string
     */
    public function getElementSetName()
    {
        return $this->elementSetName;
    }

    /**
     * @param $elementSetName
     * @return $this
     * @throws CswException
     */
    public function setElementSetName($elementSetName)
    {
        if (self::isStringAtList('ElementSetName', $elementSetName, self::ELEMENTSET, false)) {
            $this->elementSetName = $elementSetName;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'elementSetName':
                $this->setElementSetName($value);
                break;
            case 'outputSchema':
                $this->setOutputSchema($value);
                break;
            default:
                parent::setParameter($name, $value);
        }
    }
}
