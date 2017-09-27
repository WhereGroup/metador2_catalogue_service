<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Class CswException
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 * @author  Paul Schmidt <panadium@gmx.de>
 */
class CswException extends \Exception
{
    /* OGC exception code */
    const CannotLockAllFeatures = 101;
    const DuplicateStoredQueryIdValue = 102;
    const DuplicateStoredQueryParameterName = 103;
    const FeaturesNotLocked = 104;
    const InvalidLockId = 105;
    const InvalidValue = 106;
    const LockHasExpired = 107;
    const OperationParsingFailed = 108;
    const OperationProcessingFailed = 109;
    /* OWS exception code */
    const OperationNotSupported = 110;
    const MissingParameterValue = 111;
    const InvalidParameterValue = 112;
    const VersionNegotiationFailed = 113;
    const InvalidUpdateSequence = 114;
    const OptionNotSupported = 115;
    const NoApplicableCode = 116;

    /**
     * CswException constructor.
     * @param string $locator
     * @param int $code
     * @param CswException|null $previous
     */
    public function __construct($locator = "", $code = 105, CswException $previous = null)
    {
        // @TODO locator vs $previous locator
        parent::__construct($locator, $code, $previous);
    }

    /**
     * @return string|null
     */
    public function getLocator()
    {
        return $this->message && $this->hasLocator($this->code) ? $this->message : null;
    }

    /**
     * @return string
     */
    public function getCswCode()
    {
        return $this->getErrorCode($this->code);
    }

    /**
     * @return array
     */
    public function getText()
    {
        if ($this->getPrevious()) {
            return array_merge(array($this->getErrorMessage($this->code)), $this->getPrevious()->getText());
        } else {
            return array($this->getErrorMessage($this->code));
        }
    }

    /**
     * @param $code
     * @return bool|string
     */
    public function hasLocator($code)
    {
        switch ($code) {
            case self::OperationNotSupported:
            case self::CannotLockAllFeatures:
            case self::DuplicateStoredQueryIdValue:
            case self::DuplicateStoredQueryParameterName:
            case self::InvalidLockId:
            case self::InvalidValue:
            case self::OperationNotSupported:
            case self::MissingParameterValue:
            case self::InvalidParameterValue:
            case self::OptionNotSupported:
                return true;
            case self::FeaturesNotLocked:
            case self::LockHasExpired:
            case self::OperationProcessingFailed:
            case self::OperationParsingFailed:
            case self::VersionNegotiationFailed:
            case self::InvalidUpdateSequence:
            case self::NoApplicableCode:
                return false;
            default:
                return false;
        }
    }

    /**
     * @param $code
     * @return string
     */
    public function getErrorCode($code)
    {
        switch ($code) {
            case self::CannotLockAllFeatures:
                return "CannotLockAllFeatures";
            case self::DuplicateStoredQueryIdValue:
                return "DuplicateStoredQueryIdValue";
            case self::DuplicateStoredQueryParameterName:
                return "DuplicateStoredQueryParameterName";
            case self::FeaturesNotLocked:
                return "FeaturesNotLocked";
            case self::InvalidLockId:
                return "InvalidLockId";
            case self::InvalidValue:
                return "InvalidValue";
            case self::LockHasExpired:
                return "LockHasExpired";
            case self::OperationParsingFailed:
                return "OperationParsingFailed";
            case self::OperationProcessingFailed:
                return "OperationProcessingFailed";
            case self::OperationNotSupported:
                return "OperationNotSupported";
            case self::MissingParameterValue:
                return "MissingParameterValue";
            case self::InvalidParameterValue:
                return "InvalidParameterValue";
            case self::VersionNegotiationFailed:
                return "VersionNegotiationFailed";
            case self::InvalidUpdateSequence:
                return "InvalidUpdateSequence";
            case self::OptionNotSupported:
                return "OptionNotSupported";
            case self::NoApplicableCode:
                return "NoApplicableCode";
            default:
                return $this->getErrorCode(self::NoApplicableCode);
        }
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        switch ($this->code) {
            case self::CannotLockAllFeatures:
                return "Cannot lock all features";
            case self::DuplicateStoredQueryIdValue:
                return "Duplicate stored query identifier";
            case self::DuplicateStoredQueryParameterName:
                return "Duplicate stored query parameter name";
            case self::FeaturesNotLocked:
                return "Automatic locking not implemented";
            case self::InvalidLockId:
                return "Invalid lock identifier";
            case self::InvalidValue:
                return "Invalid feature or property value";
            case self::LockHasExpired:
                return "Lock identifier has expired";
            case self::OperationParsingFailed:
                return "Parsing failed: ".$this->getLocator();
            case self::OperationProcessingFailed:
                return "Operation processing failed";
            case self::OperationNotSupported:
                return "Request is for an operation that is not supported by this server/version";
            case self::MissingParameterValue:
                return "Operation request does not include a parameter value";
            case self::InvalidParameterValue:
                return "Operation request contains an invalid parameter value";
            case self::VersionNegotiationFailed:
                return "List of versions in AcceptVersions parameter value in GetCapabilities operation request"
                    ." did not include any version supported by this server";
            case self::InvalidUpdateSequence:
                return "Value of (optional) updateSequence parameter in GetCapabilities operation request is"
                    ." greater than current value of service metadata updateSequence number";
            case self::OptionNotSupported:
                return "Option is not supported";
            case self::NoApplicableCode:
                return "No other exceptionCode specified by this service and server applies to this exception";
            default:
                return $this->getErrorMessage(self::NoApplicableCode);
        }
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        switch ($this->code) {
            case self::CannotLockAllFeatures:
                return 409;
            case self::DuplicateStoredQueryIdValue:
                return 403;
            case self::DuplicateStoredQueryParameterName:
                return 403;
            case self::FeaturesNotLocked:
                return 501;
            case self::InvalidLockId:
                return 403;
            case self::InvalidValue:
                return 403;
            case self::LockHasExpired:
                return 403;
            case self::OperationParsingFailed:
                return 400;
            case self::OperationProcessingFailed:
                return 500;
            case self::OperationNotSupported:
                return 501;
            case self::MissingParameterValue:
                return 400;
            case self::InvalidParameterValue:
                return 400;
            case self::VersionNegotiationFailed:
                return 400;
            case self::InvalidUpdateSequence:
                return 400;
            case self::OptionNotSupported:
                return 501;
            case self::NoApplicableCode:
                return 500;
            default:
                return $this->getHttpStatusCode(self::NoApplicableCode);
        }
    }
}