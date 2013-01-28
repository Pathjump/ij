<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * InternshipRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InternshipRepository extends EntityRepository {

    /**
     * this function is used to get the company jobs categories ids
     * the main use in CompanyController:searchForCVAction
     * @author Mahmoud
     * @param integer $companyId
     * @return array
     */
    public function getCompanyCategoriesIds($companyId) {
        $query = $this->getEntityManager()->createQuery('
            SELECT DISTINCT ca.id
            FROM ObjectsInternJumpBundle:Internship i
            JOIN i.categories ca
            JOIN i.company c
            WHERE c.id = :companyId
            ')->setParameter('companyId', $companyId);
        $result = $query->getResult();
        $ids = array();
        if (count($result) > 0) {
            foreach ($result as $idArray) {
                $ids [] = $idArray['id'];
            }
        }
        return $ids;
    }

    /**
     * this fuction used to get hired users for company
     * @author Ahmed
     * @param int $jobsId
     */
    public function getCompanyHiredUsers($jobsId) {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT ui
            FROM ObjectsInternJumpBundle:UserInternship ui
            JOIN ui.internship i
            WHERE i.id in (:jobsId) and ui.status = :status
            ')->setParameters(array('jobsId' => $jobsId, 'status' => 'accepted'));
        return $query->getResult();
    }

    /**
     * this function will get all company jobs ids
     * @author Ahmed
     * @param int $companyId
     */
    public function getCompanyJobsIds($companyId) {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT j.id
            FROM ObjectsInternJumpBundle:Internship j
            JOIN j.company c
            WHERE c.id = :companyId and j.active = true
            ')->setParameters(array('companyId' => $companyId));
        return $query->getResult();
    }

    /**
     * this function will get company jobs except one
     * @author Ahmed
     * @param int $id
     * @param int $companyId
     */
    public function getOtherJobs($id, $companyId) {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT j
            FROM ObjectsInternJumpBundle:Internship j
            JOIN j.company c
            WHERE c.id = :companyId and j.id != :id
            ORDER BY j.createdAt DESC
            ')->setParameters(array('companyId' => $companyId, 'id' => $id));
        return $query->getResult();
    }

    /**
     * this function will get all company jobs by company id
     * @author Ahmed
     * @param int $companyId
     */
    public function getAllCompanyJobs($companyId) {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT j
            FROM ObjectsInternJumpBundle:Internship j
            JOIN j.company c
            WHERE c.id = :companyId and j.active = true
            ORDER BY j.createdAt DESC
            ')->setParameters(array('companyId' => $companyId));
        return $query->getResult();
    }

    /**
     * this function will get company jobs by company id
     * @author Ahmed
     * use InternshipController:indexAction
     * @param int $companyId
     * @param int $page
     * @param int $itemsPerPage
     */
    public function getCompanyJobs($companyId, $page, $itemsPerPage, $owner) {
        if ($page < 1) {
            return array();
        }
        $page--;
        $parameters ['companyId'] = $companyId;
        $query = '
            SELECT j
            FROM ObjectsInternJumpBundle:Internship j
            JOIN j.company c
            WHERE c.id = :companyId
            ';

        if (!$owner) {
            $query .= ' and j.active = true and j.activeFrom <= :todayDate and j.activeTo >= :todayDate';
            $parameters ['todayDate'] = new \DateTime('today');
        }

        $query .= ' ORDER BY j.createdAt DESC';
        $query = $this->getEntityManager()
                ->createQuery($query);

        $query->setParameters($parameters);
        if ($itemsPerPage) {
            $query->setFirstResult($page * $itemsPerPage);
            $query->setMaxResults($itemsPerPage);
        }
        return $query->getResult();
    }

    /**
     * this function will get all company jobs count
     * @author Ahmed
     * $companyId
     * @param int $companyId
     */
    public function countCompanyJobs($companyId, $owner) {
        $parameters ['companyId'] = $companyId;
        $query = '
            SELECT count(j.id) as jobsCount
            FROM ObjectsInternJumpBundle:Internship j
            JOIN j.company c
            WHERE c.id = :companyId
            ';
        if (!$owner) {
            $query .= ' and j.active = true and j.activeFrom <= :todayDate and j.activeTo >= :todayDate';
            $parameters ['todayDate'] = new \DateTime('today');
        }
        $query = $this->getEntityManager()
                ->createQuery($query);

        $query->setParameters($parameters);
        return $query->getResult();
    }

    /**
     * to get all the accepted users in a certain Company
     * @author Ola
     * @param type $id
     * @return type
     */
    public function getAllCompanyUsers($companyId) {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT ui
            FROM ObjectsInternJumpBundle:UserInternship ui
            JOIN ui.internship j
            JOIN j.company c
            WHERE c.id = :cId
            AND ui.status = :status
            ORDER BY j.title
            ')->setParameters(array('cId' => $companyId, 'status' => 'accepted'));
        return $query->getResult();
    }

    /**
     * this function will get all Latest jobs according to certain cv categories 
     * @author Ola
     * @param type $categories
     * @return array of jobs
     */
    public function getLatestJobs($categories) {

        $date = new \DateTime("today");
        $today = $date->format('Y-m-d');
        $query = $this->getEntityManager()
                ->createQuery('
            SELECT  DISTINCT j.id, j.title
            FROM ObjectsInternJumpBundle:Internship j
            JOIN j.company c
            JOIN j.categories cat
            WHERE j.active = true 
            AND  j.activeFrom <= :today
            AND  j.activeTo >= :today
            AND cat.id IN (:categories)
            ORDER BY j.createdAt DESC
            ');
        $query->setParameters(array('today' => $today, 'categories' => $categories));
        return $query->getResult();
    }

    /**
     * this function will get all jobs search results according to sent parameters 
     * @author Ola
     * @param string $title
     * @param string $country
     * @param string $city
     * @param string $state
     * @param int $category
     * @param string $company
     * @param int $page
     * @return array of jobs
     */
    public function getJobsSearchResult($title, $country, $city, $state, $category, $company, $page, $jobsPerPage) {

        if ($page < 1) {
            return array();
        }
        $page--;

        $today = new \DateTime("today");

        $para = array();
        $para['today'] = $today;
        $query = '
            SELECT  j, c
            FROM ObjectsInternJumpBundle:Internship j
            JOIN j.company c
            JOIN j.categories cat
            WHERE j.active = true AND  j.activeFrom <= :today AND  j.activeTo >= :today
            ';
        //j.id,j.title,j.description, j.position, j.createdAt, j.activeFrom, j.activeTo, j.country, c.id AS companyId, c.name AS companyName
        if ($title != 'empty') {
            $query .= ' and j.title LIKE :title OR j.description LIKE :title'; // OR j.position LIKE :title
            $para ['title'] = "%" . $title . "%";
        }
        if ($country != 'empty') {
            $query .= ' AND j.country = :country';
            $para ['country'] = $country;
        }
        if ($city != 'empty') {
            $query .= ' AND j.city = :city';
            $para ['city'] = $city;
        }
        if ($state != 'empty') {
            $query .= ' AND j.state = :state';
            $para ['state'] = $state;
        }
        if ($category != 'empty') {
            $query .= ' AND cat.id = :category';
            $para ['category'] = $category;
        }
        if ($company != 'empty') {
            $query .= ' AND c.id = :company';
            $para ['company'] = $company;
        }

        $query .= ' GROUP BY j.id  ORDER BY j.createdAt DESC';
        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameters($para);

        if ($jobsPerPage) {
            $query->setFirstResult($page * $jobsPerPage);
            $query->setMaxResults($jobsPerPage);
        }

        return $query->getResult();
    }

    /**
     * this function will count all jobs search results according to sent parameters 
     * @author Ola
     * @param string $title
     * @param string $country
     * @param string $city
     * @param string $state
     * @param int $category
     * @param string $company
     * @return array of jobs
     */
    public function countAllJobsSearchResults($title, $country, $city, $state, $category, $company) {

        $para = array();
        $query = '
            SELECT  COUNT(j.id) AS jobsCount
            FROM ObjectsInternJumpBundle:Internship j
            JOIN j.company c
            JOIN j.categories cat
            WHERE j.active = true 
            ';
        if ($title != 'empty') {
            $query .= ' and j.title LIKE :title OR j.description LIKE :title'; // OR j.position LIKE :title
            $para ['title'] = "%" . $title . "%";
        }
        if ($country != 'empty') {
            $query .= ' AND j.country = :country';
            $para ['country'] = $country;
        }
        if ($city != 'empty') {
            $query .= ' AND j.city = :city';
            $para ['city'] = $city;
        }
        if ($state != 'empty') {
            $query .= ' AND j.state = :state';
            $para ['state'] = $state;
        }
        if ($category != 'empty') {
            $query .= ' AND cat.id = :category';
            $para ['category'] = $category;
        }
        if ($company != 'empty') {
            $query .= ' AND c.id = :company';
            $para ['company'] = $company;
        }

//        $query .= ' GROUP BY j.id ORDER BY j.createdAt DESC';
        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameters($para);

        $result = $query->getResult();
        return $result;
        if ($result)
            return $result['0']['jobsCount'];
        else
            return $result;
    }

}