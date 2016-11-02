<?php

namespace AppBundle\Model;

/**
 * User after mapping has been done.
 */
class User
{
    /**
     * @var string
     */
    private $ipAddress = '';

    /**
     * @var string
     */
    private $institution = '';

    /**
     * @var array
     */
    private $products = [];

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     *
     * @return User
     */
    public function setIpAddress(string $ipAddress): User
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstitution(): string
    {
        return $this->institution;
    }

    /**
     * @param string $institution
     *
     * @return User
     */
    public function setInstitution(string $institution): User
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param array $products
     *
     * @return User
     */
    public function setProducts(array $products): User
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @param string $product
     */
    public function addProduct($product)
    {
        $this->products[] = $product;
    }
}
