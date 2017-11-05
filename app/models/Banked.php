<?php

class Banked extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $bankedId;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $amount;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $description;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $transactionReference;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $userId;

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
    public $status;

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
        return 'banked';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Banked[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Banked
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /*CREATE TABLE `covenant`.`Banked` ( `bankedId` INT NOT NULL AUTO_INCREMENT , `amount` INT NOT NULL , `description` VARCHAR(500) NOT NULL , `transactionReference` VARCHAR(500) NOT NULL , `userId` INT NOT NULL , `memberId` INT NOT NULL , `status` INT NOT NULL , `createdAt` DATETIME NOT NULL , `updatedAt` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`bankedId`)) ENGINE = InnoDB;

    ALTER TABLE `Banked` CHANGE `status` `status` INT(11) NULL DEFAULT '0';

*/

}
