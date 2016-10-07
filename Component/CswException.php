<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of CswException
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class CswException extends \Exception
{
    # @TODO check and add errors as const, into getErrorCode, getErrorMessage
    const InvalidParameterValue    = 101;
    const OperationNotSupported    = 100;
    const MissingParameterValue    = 102;
    const VersionNegotiationFailed = 103;
    const NoApplicableCode         = 104;
    const InvalidUpdateSequence    = 105;
    const ParsingError             = 106;

    public function __construct($locator = "", $code = 105, CswException $previous = null)
    {
        // @TODO locator vs $previous locator
        parent::__construct($locator, $code, $previous);
    }

    public function getLocator()
    {
        return $this->message && $this->hasLocator($this->code) ? $this->message : null;
    }

    public function getCswCode()
    {
        return $this->getErrorCode($this->code);
    }

    public function getText()
    {
        if ($this->getPrevious()) {
            return array_merge(array($this->getErrorMessage($this->code)), $this->getPrevious()->getText());
        } else {
            return array($this->getErrorMessage($this->code));
        }
    }

    public function hasLocator($code)
    {
        switch ($code) {
            case self::OperationNotSupported :
                return true;
            case self::MissingParameterValue :
                return true;
            case self::InvalidParameterValue :
                return true;
            case self::VersionNegotiationFailed :
                return false;
            case self::InvalidUpdateSequence :
                return false;
            case self::NoApplicableCode :
                return false;
            case self::ParsingError :
                return false;
            default :
                return "NOTDEFINED";
        }
    }

    public function getErrorCode($code)
    {
        switch ($code) {
            case self::OperationNotSupported :
                return "OperationNotSupported";
            case self::MissingParameterValue :
                return "MissingParameterValue";
            case self::InvalidParameterValue :
                return "InvalidParameterValue";
            case self::VersionNegotiationFailed :
                return "VersionNegotiationFailed";
            case self::InvalidUpdateSequence :
                return "InvalidUpdateSequence";
            case self::NoApplicableCode :
                return "NoApplicableCode";
            case self::ParsingError :
                return "ParsingError";
            default :
                return "ErrorCodeNotDefined";
        }
    }

    public function getErrorMessage($code)
    {
        switch ($code) {
            case self::OperationNotSupported :
                return "Request is for an operation that is not supported by this server/version";
            case self::MissingParameterValue :
                return "Operation request does not include a parameter value";
            case self::InvalidParameterValue :
                return "Operation request contains an invalid parameter value";
            case self::VersionNegotiationFailed :
                return "List of versions in AcceptVersions parameter value in GetCapabilities operation request"
                    . " did not include any version supported by this server";
            case self::InvalidUpdateSequence :
                return "Value of (optional) updateSequence parameter in GetCapabilities operation request is"
                    . " greater than current value of service metadata updateSequence number";
            case self::NoApplicableCode :
                return "No other exceptionCode specified by this service and server applies to this exception";
            case self::ParsingError :
                return "Parse error: " . $this->getLocator();
            default :
                return "Error is not defined";
        }
    }
}