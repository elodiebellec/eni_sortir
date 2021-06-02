<?php


namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class OutingsFilter
{


    private $name;
    private $dateBegin;
    private $dateEnd;
    private $site;
    private $isRegistered;

    private $isNotRegistered;

    private $isOutDated;



    private  $isPlanner;

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

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->dateBegin;
    }

    public function setDateBegin(\DateTimeInterface $dateBegin=null): self
    {
        $this->dateBegin = $dateBegin;

        return $this;
    }


    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd =null): self
    {
        $this->dateEnd = $dateEnd;

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