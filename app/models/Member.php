<?php

class Member extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $memberId;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    public $memberName;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $memberIdNumber;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $memberPhoneNumber;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $memberRole;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $membershipNumber;

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
        return 'member';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Member[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Member
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
