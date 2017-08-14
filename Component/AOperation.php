<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw as CswEntity;

/**
 * Description of Operation
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
abstract class AOperation
{
    public static $ALLOW_GET = true;

    public static $ALLOW_POST = true;

    /**
     * The element prefix for csw namespace
     */
    const PREFIX = 'csw';

    /**
     * The uri for csw namespace
     */
    const NSPACE = 'http://www.opengis.net/cat/csw/2.0.2';

    /**
     * The service name
     */
    const SERVICE = 'CSW';

    /**
     * The version
     */
    const VERSION = '2.0.2';

    const OUTPUT_FORMAT = '';
//
//    /**
//     * The supported versions
//     * @var $array $VERSIONLIST
//     */
//    static $VERSIONLIST     = array('2.0.2');

    /**
     * The list of key values pair to find parameters at request.
     * @var array $parameterMap
     */
    protected static $parameterMap = array();
//
//    /**
//     * The operation's name
//     * @var string $name
//     */
//    protected $name;
////
//    /**
//     * The csw instance
//     * @var \Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw $csw
//     */
//    protected $csw;

    /**
     * List with exceptions
     * @var array $exceptions
     */
    protected $exceptions;
//
//    /**
//     * The url to request the operation via http GET
//     * @var string $httpGet
//     */
//    protected $httpGet;
//
//    /**
//     * The url to request the operation via http POST
//     * @var string $httpPost
//     */
//    protected $httpPost;
//
//    /**
//     * List with supported response formats
//     * @var array $outputFormatList
//     */
//    protected $outputFormatList;
//
//    /**
//     * The list with all supported POST encodings.
//     * @var array $postEncodingList
//     */
//    protected $postEncodingList = array('XML');
//
//    /**
//     * The POST encoding for operation's request
//     * @var string $postEncoding
//     */
//    protected $postEncoding;

    /* request parameters */

    /**
     * The service name (CSW)
     * @var string $service
     */
    protected $service;

    /**
     * The requested versions
     * @var string $version
     */
    protected $version;

    /**
     * The requested output formats
     * @var array $outputFormat
     */
    protected $outputFormat;

    /**
     * Template to response an operation result.
     * @var string $template
     */
    protected $template;

    protected $entity;

    /**
     * Creates an instance.
     * @param \Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw $csw
     * @param array $configuration configuration
     */
    public function __construct(CswEntity $entity)
    {
        $this->entity = $entity;
//        $this->csw = $csw;
//        $this->httpGet = $configuration['http']['get'] ? $this->csw->getHttpGet() : null;
//        $this->httpPost = $configuration['http']['post'] ? $this->csw->getHttpPost() : null;
//        $this->outputFormatList = array_keys($configuration['outputFormatList']);
//        $this->outputFormat = $this->outputFormatList[0]; # !!! IMPORTANT
//        $this->exceptions = array();
//        $this->templates = $configuration['outputFormatList'];
//
//        $this->postEncoding = $this->postEncodingList[0]; # !!! IMPORTANT

        $this->version = self::VERSION;
    }

    /**
     * Destroies an instance.
     * @param \Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw $csw
     * @param array $configuration configuration
     */
    public function __destruct()
    {
//        unset(
//            $this->csw, $this->httpGet, $this->httpPost, $this->outputFormatList, $this->outputFormat, $this->exceptions
//        );
    }

    /**
     * Returns a parameter map.
     * @return array parameter map
     */
    abstract public static function getGETParameterMap();

    /**
     * Returns a parameter map.
     * @return array parameter map
     */
    abstract public static function getPOSTParameterMap();

    /**
     * Returns an opreation's name
     * @return string name
     */
    public function getEntity()
    {
        return $this->entity;
    }
//
//    /**
//     * Returns an url to request the operation via http GET.
//     * @return string url
//     */
//    public function getHttpGet()
//    {
//        return $this->httpGet;
//    }
//
//    /**
//     * Returns an url to request the operation via http POST.
//     * @return string url
//     */
//    public function getHttpPost()
//    {
//        return $this->httpPost;
//    }

    /**
     * Returns a service name (CSW).
     * @return string url
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Returns a list with requested version/s.
     * @return string url
     */
    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        if ($version === self::VERSION) {
            $this->version = $version;
        }
    }

    /**
     * Sets the service name.
     * @param string $service
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation
     */
    public function setService($service)
    {
        if ($service === self::SERVICE) {
            $this->service = $service;
        } elseif ($service === null || $service === '') {
            $this->addCswException('service', CswException::InvalidParameterValue);
        } else {
            $this->addCswException('service', CswException::MissingParameterValue);
        }

        return $this;
    }

    /**
     * @param array $outputFormat
     */
    public function setOutputFormat($value)
    {
        if ($value && is_string($value)) { # GET request
            $outputFormat = self::parseCsl($value);
            if ($this->outputFormat !== $outputFormat) {
                $this->addCswException('outputFormat', CswException::InvalidParameterValue);
            } else {
                $this->outputFormat = $outputFormat;
            }
        } elseif ($value && !in_array($this->outputFormat, $value)) {
            $this->addCswException('outputFormat', CswException::InvalidParameterValue);
        }
    }

    /**
     * Adds an CswException into the exception's list.
     * @param string $locator an exception locator
     * @param integer $code a CswException code
     */
    public function addCswException($locator, $code)
    {
        $found = false;
        foreach ($this->exceptions as $exception) {
            if ($exception->getMessage() === $locator && $exception->getCode() === $code) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->exceptions[] = new CswException($locator, $code);
        }
    }

    /**
     * Checks und sets a request parameter.
     * @param string $name parameter name
     * @param mixed $value parameter value
     * @throws \Exception: (developing only)  Exception if a mandatory parameter is not checked
     */
    protected function setParameter($name, $value)
    {
        switch ($name) {
            case 'version':
                $this->setVersion($value);
                break;
            case 'service':
                $this->setService($value);
                break;
            case 'outputFormat':
                $this->setOutputFormat($value);
                break;
            case 'request':
                break;
            default:
                throw new \Exception('!!!!!not defined parameter:'.$name);
        }
    }

    /**
     * Checks if all parameters are valid.
     * @return boolean true if all request parameter are valid otherwise false.
     * @throws \Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException if a parameter isn't valid.
     */
    protected function validateParameter()
    {
        if ($this->version !== self::VERSION) {
            $this->addCswException('version', CswException::InvalidParameterValue);
        }
        if (count($this->exceptions) === 0) {
            return true;
        } else {
            $exception = null;
            foreach ($this->exceptions as $exc) {
                if ($exception) {
                    $exception = new CswException($exc->getMessage(), $exc->getCode(), $exception->getPrevious());
                } else {
                    $exception = new CswException($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
                }
            }
            throw $exception;
        }
    }

    public function setParameters(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }
        $this->validateParameter();
    }

    public function getContentSet()
    {
        return new ContentSet($this);
    }

    /**
     * Creates and returns the operation result.
     * @param \Plugins\WhereGroup\CatalogueServiceBundle\Component\AParameterHandler $handler
     * @return string response
     */
    public function createResult($templating)
    {
        return $this->render($templating);
    }

    /**
     * Parses a comma separated list.
     * @param string $string a string to parse
     * @param boolean $trim if true: trim by parsing otherwise parse wothout trimming.
     * @return array with the list items
     */
    protected static function parseCsl($string, $trim = true)
    {
        return $trim ? preg_split('/\s?,\s?/', trim($string)) : preg_split('/,/', $string);
    }

    /**
     * Checks if at least one item is in both arrays.
     * @param array $array1
     * @param array $array2
     * @return boolean true
     */
    protected static function hasJointField(array $array1, array $array2)
    {
        foreach ($array1 as $item) {
            if (in_array($item, $array2)) {
                return true;
            }
        }

        return false;
    }

    protected function isListAtList($name, $list, array $values, $mandatory = false)
    {
        $result = null;
        foreach ($list as $item) {
            if ($result === null) {
                $result = $this->isStringAtList($item, $item, $values, $mandatory);
            } elseif (!$this->isStringAtList($item, $item, $values, $mandatory)) {
                $result = false;
            }
        }

        return $result !== null && $result !== false;
    }

    protected function isStringAtList($name, $value, array $values, $mandatory = false)
    {
        $validString = $value !== null && is_string($value) && $value !== '';
        if ($validString && in_array($value, $values)) {
            return true;
        } elseif ($validString && !in_array($value, $values)) {
            $this->addCswException($name, CswException::InvalidParameterValue);

            return false;
        } elseif ($mandatory && (!$validString || !in_array($value, $values))) {
            $this->addCswException($name, CswException::InvalidParameterValue);

            return false;
        } else {
            return false;
        }
    }

    protected static function normalizeString($string)
    {
        if ($string === null || $string === '' || trim($string) === '') {
            return null;
        } else {
            return trim($string);
        }
    }

    protected function getInteger($name, $intToTest)
    {
        if (is_integer($intToTest)) {
            return $intToTest;
        } elseif (ctype_digit(trim($intToTest))) {
            return intval(trim($intToTest));
        } else {
            $this->addCswException($name, CswException::InvalidParameterValue);

            return null;
        }
    }

    protected function getPositiveInteger($name, $intToTest)
    {
        if (($int = self::getInteger($name, $intToTest)) !== null) {
            if ($int >= 0) {
                return $int;
            } else {
                $this->addCswException($name, CswException::InvalidParameterValue);

                return null;
            }
        } else {
            return null;
        }
    }

    /**
     *
     * @param string $name
     * @param float | integer $numberToTest
     * @param float | integer $number
     * @return integer | null
     */
    protected function getGreaterThan($name, $numberToTest, $number)
    {
        if ((is_integer($numberToTest) || is_float($numberToTest)) && $numberToTest > $number) {
            return $numberToTest;
        } else {
            $this->addCswException($name, CswException::InvalidParameterValue);

            return null;
        }
    }

    /**
     * Renders a operation's result and returns as string
     * @return string the operation's result
     */
    abstract protected function render($templating);
}
