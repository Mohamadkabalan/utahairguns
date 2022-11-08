<?php

namespace FedExVendor\FedEx\InFlightShipmentService\ComplexType;

use FedExVendor\FedEx\AbstractComplexType;
/**
 * Party
 *
 * @author      Jeremy Dunn <jeremy@jsdunn.info>
 * @package     PHP FedEx API wrapper
 * @subpackage  In Flight Shipment Service
 *
 * @property string $AccountNumber
 * @property TaxpayerIdentification[] $Tins
 * @property Contact $Contact
 * @property Address $Address
 */
class Party extends \FedExVendor\FedEx\AbstractComplexType
{
    /**
     * Name of this complex type
     *
     * @var string
     */
    protected $name = 'Party';
    /**
     * Set AccountNumber
     *
     * @param string $accountNumber
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        $this->values['AccountNumber'] = $accountNumber;
        return $this;
    }
    /**
     * Set Tins
     *
     * @param TaxpayerIdentification[] $tins
     * @return $this
     */
    public function setTins(array $tins)
    {
        $this->values['Tins'] = $tins;
        return $this;
    }
    /**
     * Set Contact
     *
     * @param Contact $contact
     * @return $this
     */
    public function setContact(\FedExVendor\FedEx\InFlightShipmentService\ComplexType\Contact $contact)
    {
        $this->values['Contact'] = $contact;
        return $this;
    }
    /**
     * Set Address
     *
     * @param Address $address
     * @return $this
     */
    public function setAddress(\FedExVendor\FedEx\InFlightShipmentService\ComplexType\Address $address)
    {
        $this->values['Address'] = $address;
        return $this;
    }
}
