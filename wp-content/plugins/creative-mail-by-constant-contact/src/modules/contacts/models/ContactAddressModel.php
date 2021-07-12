<?php

namespace CreativeMail\Modules\Contacts\Models;

class ContactAddressModel
{
    public $countryCode;
    public $postalCode;
    public $state;
    public $stateCode;
    public $address;
    public $address2;
    public $city;

    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setStateCode($stateCode)
    {
        $this->stateCode = $stateCode;
    }

    public function getStateCode()
    {
        return $this->stateCode;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function toArray()
    {
        return array(
            "country_code" => $this->getCountryCode(),
            "state_code" => $this->getStateCode(),
            "state" => $this->getState(),
            "postal_code" => $this->getPostalCode(),
            "address" => $this->getAddress(),
            "address2" => $this->getAddress2(),
            "city" => $this->getCity()
        );
    }
}