<?php

class Savings extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $savingsId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=50, nullable=false)
     */
    public $savingsAmount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $memberId;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $createdAt;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $updatedAt;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'savings';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Savings[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Savings
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
