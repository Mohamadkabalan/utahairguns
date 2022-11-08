<?php

namespace FedExVendor\FedEx\LocationsService\ComplexType;

use FedExVendor\FedEx\AbstractComplexType;
/**
 * LocationSupportedPackageDetail
 *
 * @author      Jeremy Dunn <jeremy@jsdunn.info>
 * @package     PHP FedEx API wrapper
 * @subpackage  Locations Service
 *
 * @property Weight $Weight
 * @property Dimensions $Dimensions
 * @property string[] $ServiceOptions
 */
class LocationSupportedPackageDetail extends \FedExVendor\FedEx\AbstractComplexType
{
    /**
     * Name of this complex type
     *
     * @var string
     */
    protected $name = 'LocationSupportedPackageDetail';
    /**
     * Set Weight
     *
     * @param Weight $weight
     * @return $this
     */
    public function setWeight(\FedExVendor\FedEx\LocationsService\ComplexType\Weight $weight)
    {
        $this->values['Weight'] = $weight;
        return $this;
    }
    /**
     * Set Dimensions
     *
     * @param Dimensions $dimensions
     * @return $this
     */
    public function setDimensions(\FedExVendor\FedEx\LocationsService\ComplexType\Dimensions $dimensions)
    {
        $this->values['Dimensions'] = $dimensions;
        return $this;
    }
    /**
     * Set ServiceOptions
     *
     * @param string $serviceOptions
     * @return $this
     */
    public function setServiceOptions($serviceOptions)
    {
        $this->values['ServiceOptions'] = $serviceOptions;
        return $this;
    }
}
