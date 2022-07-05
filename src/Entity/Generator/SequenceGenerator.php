<?php
/**
 * Class SequenceGenerator
 * @package App\Entity\Generator
 *
 * since: 05.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Generator;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Elastica\Util;

class SequenceGenerator extends AbstractIdGenerator
{

    public function generate(EntityManager $em, $entity)
    {
        if ($entity === null) {
            $entity = $this;
        }
        $scope = Util::toSnakeCase(substr(strrchr(get_class($entity), '\\'), 1));
        $sql = "INSERT INTO `app_sequence_id` (`name`) VALUES ('$scope')";
        try {
            $em->getConnection()->executeQuery($sql);
            return $em->getConnection()->lastInsertId('app_sequence_id');
        } catch (Exception $e) {
        }
        return 0;
    }
}

/*
   CREATE TABLE `app_sequence_id` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`id`)
)
COLLATE='latin1_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=100
;
*/