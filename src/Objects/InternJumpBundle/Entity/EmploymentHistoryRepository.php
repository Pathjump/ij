<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * EmploymentHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EmploymentHistoryRepository extends EntityRepository
{
        /**
     * This is for getting all user's Employment History
     * @author Ola
     * @param type $uid User Id
     * @return null
     */
        public function getAllExperince($uid){
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT e
            FROM ObjectsInternJumpBundle:EmploymentHistory e
            JOIN e.user u
            WHERE u.id = :uid
            ')->setParameter('uid', $uid);
        
            return $query->getResult();

    }
}