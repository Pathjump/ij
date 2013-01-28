<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CVCategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CVCategoryRepository extends EntityRepository {

    /**
     * this function will get all parent categories with the child objects
     * main use in CompanyController:searchForCVAction
     * @author Mahmoud
     */
    public function getAllParentCategories() {
        $query = $this->getEntityManager()
                ->createQuery('
            SELECT c, sc
            FROM ObjectsInternJumpBundle:CVCategory c
            LEFT JOIN c.subCategories sc
            WHERE c.parentCategory IS NULL
            ');
        return $query->getResult();
    }

    /**
     * to get all categories
     * @author Ola
     */
    public function getAllCategories() {
        $query = $this->getEntityManager()
                ->createQuery('
            SELECT cat.id, cat.name
            FROM ObjectsInternJumpBundle:CVCategory cat
            ');
        return $query->getResult();
    }

    /**
     * This function is to get auto complete categories according to text comes from User Search page
     * @author Ola
     * @param string $text
     * @return array of categories
     */
    public function autocompletecategories($text) {
        $em = $this->getEntityManager();
        $dql = "SELECT cat.id, cat.name 
           FROM ObjectsInternJumpBundle:CVCategory cat
           WHERE cat.name LIKE '" . $text . "%'     
           ORDER BY cat.name asc";

        $result = $em->createQuery($dql)
                ->getResult();
        return $result;
    }

}