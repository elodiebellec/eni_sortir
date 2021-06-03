<?php


namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class OutingsFilter
{


    private $name;
    private $dateBeginFilter;
    private $dateEndFilter;
    private $site;
    private $isRegistered;
    private $isNotRegistered;
    private $isOutDated;
    private  $isPlanner;

    /**
     * @return mixed
     */
    public function getDateBeginFilter()
    {
        return $this->dateBeginFilter;
    }

    /**
     * @param mixed $dateBeginFilter
     */
    public function setDateBeginFilter($dateBeginFilter): void
    {
        $this->dateBeginFilter = $dateBeginFilter;
    }

    /**
     * @return mixed
     */
    public function getDateEndFilter()
    {
        return $this->dateEndFilter;
    }

    /**
     * @param mixed $dateEndFilter
     */
    public function setDateEndFilter($dateEndFilter): void
    {
        $this->dateEndFilter = $dateEndFilter;
    }



    /**
     * @return mixed
     */
    public function getIsPlanner()
    {
        return $this->isPlanner;
    }

    /**
     * @param mixed $isPlanner
     */
    public function setIsPlanner($isPlanner): void
    {
        $this->isPlanner = $isPlanner;
    }

    /**
     * @return mixed
     */
    public function getIsRegistered()
    {
        return $this->isRegistered;
    }

    /**
     * @param mixed $isRegistered
     */
    public function setIsRegistered($isRegistered): void
    {
        $this->isRegistered = $isRegistered;
    }

    /**
     * @return mixed
     */
    public function getIsNotRegistered()
    {
        return $this->isNotRegistered;
    }

    /**
     * @param mixed $isNotRegistered
     */
    public function setIsNotRegistered($isNotRegistered): void
    {
        $this->isNotRegistered = $isNotRegistered;
    }

    /**
     * @return mixed
     */
    public function getIsOutDated()
    {
        return $this->isOutDated;
    }

    /**
     * @param mixed $isOutDated
     */
    public function setIsOutDated($isOutDated): void
    {
        $this->isOutDated = $isOutDated;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(?string  $site): self
    {
        $this->site = $site;

        return $this;
    }


}