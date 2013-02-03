<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CVRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CVRepository extends EntityRepository {

    /**
     * this function is used to search for user cvs
     * the main use in CompanyController:searchForCVAction
     * @param integer $page
     * @param integer $maxResults
     * @return array the count index contains the count of returned objects, the entities index contains the objects
     */
    public function searchForCVs($searchString, $countryId, $cityId, $stateId, $selectedSkillsIds, $selectedCategories, $experienceYears, $page = 1, $maxResults = 10) {
        $data = array(
            'count' => 0,
            'enities' => array()
        );
        if ($page >= 1) {
            $page--;
            $parameters = array();
            $mainQuery = '
                FROM ObjectsInternJumpBundle:CV cv
                JOIN cv.user u
                LEFT JOIN cv.skills s
                LEFT JOIN cv.employmentHistory e
                LEFT JOIN cv.categories c
                WHERE cv.isActive = 1
                AND u.locked = 0
                AND u.enabled = 1
                ';
            if ($searchString) {
                $mainQuery .= ' AND (s.title LIKE :searchString OR c.name LIKE :searchString)';
                $parameters['searchString'] = "%$searchString%";
            }
            if ($countryId) {
                $mainQuery .= ' AND u.country = :countryId';
                $parameters['countryId'] = $countryId;
            }
            if ($cityId) {
                $mainQuery .= ' AND u.city = :cityId';
                $parameters['cityId'] = $cityId;
            }
            if ($stateId) {
                $mainQuery .= ' AND u.state = :stateId';
                $parameters['stateId'] = $stateId;
            }
            if (is_array($selectedSkillsIds) && !empty($selectedSkillsIds)) {
                $mainQuery .= ' AND s.id IN(:selectedSkillsIds)';
                $parameters['selectedSkillsIds'] = $selectedSkillsIds;
            }
            if (is_array($selectedCategories) && !empty($selectedCategories)) {
                $mainQuery .= ' AND c.id IN(:selectedCategories) OR c.parentCategory IN(:selectedCategories)';
                $parameters['selectedCategories'] = $selectedCategories;
            }
            if (is_array($experienceYears) && !empty($experienceYears)) {
                $mainQuery .= " AND (";
                foreach ($experienceYears as $key => $experienceYear) {
                    if ($key != 0) {
                        $mainQuery .= " OR ";
                    }
                    $mainQuery .= "(DATE_DIFF(e.endedIn, e.startedFrom) BETWEEN :experienceYearStart$key AND :experienceYearEnd$key) OR (e.isCurrent = 1 AND (DATE_DIFF(CURRENT_DATE(), e.startedFrom) BETWEEN :experienceYearStart$key AND :experienceYearEnd$key))";
                    $parameters["experienceYearStart$key"] = $experienceYear * 365;
                    $parameters["experienceYearEnd$key"] = ($experienceYear * 365) + 365;
                }
                $mainQuery .= ")";
            }
            $queryEnd = 'ORDER BY cv.totalPoints DESC';
            $query = $this->getEntityManager()->createQuery("SELECT DISTINCT cv, u $mainQuery $queryEnd")->setParameters($parameters);
            $countQuery = $this->getEntityManager()->createQuery("SELECT COUNT(DISTINCT cv.id) $mainQuery")->setParameters($parameters);
            $query->setFirstResult($page * $maxResults);
            $query->setMaxResults($maxResults);
            $result = $countQuery->getResult();
            $data['count'] = $result[0][1];
            $data['entities'] = $query->getResult();
        }
        return $data;
    }

    /**
     * this function will get suitable active user cvs for new job
     * @author Ahmed edited by Ola
     * @param array $categoryIdsArray
     */
    public function getNewJobSuitableCvs($categoryIdsArray,$country = NULL, $state = NULL) {
        
        $where = ""; 
        //if country and state are set with values
        if($country != NULL && $state != NULL){
            $where = "WHERE c.isActive = true and cat.id in (:categoryIdsArray) AND u.country = '$country' AND u.state = '$state'"; 
        }
        //if country is set and state not set
        elseif($country != NULL && $state == NULL){
            $where = "WHERE c.isActive = true and cat.id in (:categoryIdsArray) AND u.country = '$country'"; 
        }
        //both are not set [both null]
        else{
            $where = "WHERE c.isActive = true and cat.id in (:categoryIdsArray)";  
        }
        echo $where."<br>";
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT c.id as cvId,u.id as userId,u.email
            FROM ObjectsInternJumpBundle:CV c
            JOIN c.user u
            JOIN c.categories cat
            '.$where.'
            group by u.email
            ')->setParameter('categoryIdsArray', $categoryIdsArray);

        return $query->getResult();
    }

    public function getCv($cvId) {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT c,u,s
            FROM ObjectsInternJumpBundle:CV c
            JOIN c.user u
            LEFT JOIN u.socialAccounts s
            WHERE c.id = :cvId
            ')->setParameter('cvId', $cvId);

        try {
            return $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /**
     * This is for getting all user's active cvs
     * @author Ola
     * @param type $cvId
     * @return null
     */
    public function getAllCvs($uid) {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT c
            FROM ObjectsInternJumpBundle:CV c
            JOIN c.user u
            WHERE u.id = :uid AND c.isActive= 1
            ')->setParameter('uid', $uid);

        return $query->getResult();
    }

    public function PerformActionsOnUserCvs($cvsIds) {
        $para = array();
        $query = '
            SELECT cv
            FROM ObjectsInternJumpBundle:CV cv
            WHERE cv.id IN (:cvsId)
            ';

        $para ['cvsId'] = $cvsIds;

        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameters($para);

        $result = $query->getResult();
        return $result;
    }

}