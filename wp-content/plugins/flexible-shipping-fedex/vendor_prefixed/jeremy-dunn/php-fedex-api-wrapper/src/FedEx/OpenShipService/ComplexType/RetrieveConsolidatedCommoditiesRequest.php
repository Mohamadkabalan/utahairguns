<?php

namespace FedExVendor\FedEx\OpenShipService\ComplexType;

use FedExVendor\FedEx\AbstractComplexType;
/**
 * RetrieveConsolidatedCommoditiesRequest
 *
 * @author      Jeremy Dunn <jeremy@jsdunn.info>
 * @package     PHP FedEx API wrapper
 * @subpackage  OpenShip Service
 *
 * @property WebAuthenticationDetail $WebAuthenticationDetail
 * @property ClientDetail $ClientDetail
 * @property TransactionDetail $TransactionDetail
 * @property VersionId $Version
 * @property ConsolidationKey $ConsolidationKey
 */
class RetrieveConsolidatedCommoditiesRequest extends \FedExVendor\FedEx\AbstractComplexType
{
    /**
     * Name of this complex type
     *
     * @var string
     */
    protected $name = 'RetrieveConsolidatedCommoditiesRequest';
    /**
     * Descriptive data to be used in authentication of the sender's identity (and right to use FedEx web services).
     *
     * @param WebAuthenticationDetail $webAuthenticationDetail
     * @return $this
     */
    public function setWebAuthenticationDetail(\FedExVendor\FedEx\OpenShipService\ComplexType\WebAuthenticationDetail $webAuthenticationDetail)
    {
        $this->values['WebAuthenticationDetail'] = $webAuthenticationDetail;
        return $this;
    }
    /**
     * Set ClientDetail
     *
     * @param ClientDetail $clientDetail
     * @return $this
     */
    public function setClientDetail(\FedExVendor\FedEx\OpenShipService\ComplexType\ClientDetail $clientDetail)
    {
        $this->values['ClientDetail'] = $clientDetail;
        return $this;
    }
    /**
     * Set TransactionDetail
     *
     * @param TransactionDetail $transactionDetail
     * @return $this
     */
    public function setTransactionDetail(\FedExVendor\FedEx\OpenShipService\ComplexType\TransactionDetail $transactionDetail)
    {
        $this->values['TransactionDetail'] = $transactionDetail;
        return $this;
    }
    /**
     * Set Version
     *
     * @param VersionId $version
     * @return $this
     */
    public function setVersion(\FedExVendor\FedEx\OpenShipService\ComplexType\VersionId $version)
    {
        $this->values['Version'] = $version;
        return $this;
    }
    /**
     * If provided, identifies the consolidation from which commodities will be retrieved.
     *
     * @param ConsolidationKey $consolidationKey
     * @return $this
     */
    public function setConsolidationKey(\FedExVendor\FedEx\OpenShipService\ComplexType\ConsolidationKey $consolidationKey)
    {
        $this->values['ConsolidationKey'] = $consolidationKey;
        return $this;
    }
}
