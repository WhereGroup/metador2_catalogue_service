<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

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
    const ELEMENTSET = array('full');//array('brief', 'summary', 'full');
    const RESULTTYPE = array('hits', 'results', 'validate');

    /**
     * @var string
     */
    protected $outputSchema;

    /**
     * @var string
     */
    protected $elementSetName;

    /**
     * {@inheritdoc}
     */
    public function __construct(\Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $entity)
    {
        parent::__construct($entity);
        $this->outputSchema = "http://www.isotc211.org/2005/gmd";
        $this->elementSetName = 'summary';
    }

    /**
     * Returns the output schema.
     * @return string output schema
     */
    public function getOutputSchema()
    {
        return $this->outputSchema;
    }

    /**
     * @param mixed $outputSchema
     * @return FindRecord
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
     * @param string $elementSetName
     * @return FindRecord
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