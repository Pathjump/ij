<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PersonalQuestionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonalQuestionRepository extends EntityRepository {

    /**
     * this function used to get personal questions with answer for this user
     * @author Ahmed
     * @param int $getId
     */
    public function getUserPersonalQuestions($userId) {
        $query = $this->getEntityManager()->createQuery("
            SELECT q.question,q.id,(select a.answer FROM ObjectsInternJumpBundle:PersonalQuestionAnswer a where a.user = :userId and a.question = q.id ) as userAnswer
            FROM ObjectsInternJumpBundle:PersonalQuestion q
            ")->setParameters(array('userId' => $userId));
        return $query->getResult();
    }

}