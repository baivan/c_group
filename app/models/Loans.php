<?php

class Loans extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $loanId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $memberId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $userId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=50, nullable=false)
     */
    public $loanAmount;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $amountToPay;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $interestRate;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $repaidAmount;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $loanOfferDate;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $loanRepayDate;

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
        return 'loans';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Loans[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Loans
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
