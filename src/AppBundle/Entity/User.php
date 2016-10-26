<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="startIpAddress", type="bigint")
     */
    private $startIpAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="endIpAddress", type="bigint")
     */
    private $endIpAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="institution", type="string", length=255, nullable=true)
     */
    private $institution;

    /**
     * @var string
     *
     * @ORM\Column(name="product", type="string", length=30)
     */
    private $product;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set startIpAddress.
     *
     * @param int $startIpAddress
     *
     * @return User
     */
    public function setStartIpAddress($startIpAddress)
    {
        $this->startIpAddress = $startIpAddress;

        return $this;
    }

    /**
     * Get startIpAddress.
     *
     * @return int
     */
    public function getStartIpAddress()
    {
        return $this->startIpAddress;
    }

    /**
     * Set endIpAddress.
     *
     * @param int $endIpAddress
     *
     * @return User
     */
    public function setEndIpAddress($endIpAddress)
    {
        $this->endIpAddress = $endIpAddress;

        return $this;
    }

    /**
     * Get endIpAddress.
     *
     * @return int
     */
    public function getEndIpAddress()
    {
        return $this->endIpAddress;
    }

    /**
     * Set institution.
     *
     * @param string $institution
     *
     * @return User
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution.
     *
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set product.
     *
     * @param string $product
     *
     * @return User
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }
}
