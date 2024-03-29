<?php

namespace Objects\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository implements UserProviderInterface {

    /**
     * get not complete resume users from 3,7,15 days
     * @author ahmed
     * @param datetime $threeDate
     * @param datetime $sevenDate
     * @param datetime $fifteenDate
     * @param int $roleActiveId
     */
    public function getCharacterQuizUsers($threeDate, $sevenDate, $fifteenDate, $roleActiveId) {
        $query = $this->getEntityManager()->createQuery("
            select u From ObjectsUserBundle:User u
            join u.userRoles r
            where r.id = :roleId and u.score is null and ( u.createdAt = :threeDate or u.createdAt = :sevenDate or u.createdAt = :fifteenDate )
            ")->setParameters(array(
            'roleId' => $roleActiveId,
            'threeDate' => $threeDate,
            'sevenDate' => $sevenDate,
            'fifteenDate' => $fifteenDate
        ));

        return $query->getResult();
    }

    /**
     * get not complete resume users from 3,7,15 days
     * @author ahmed
     * @param datetime $threeDate
     * @param datetime $sevenDate
     * @param datetime $fifteenDate
     * @param int $roleActiveId
     */
    public function getNotCompleteResumeUsers($threeDate, $sevenDate, $fifteenDate, $roleActiveId) {
        $query = $this->getEntityManager()->createQuery("
            select u.email,u.loginName,
                   (select count(cv.id) from ObjectsInternJumpBundle:CV cv where cv.user = u.id ) as cvCount,
                   (select count(e.id) from ObjectsInternJumpBundle:Education e where e.user = u.id ) as educationCount
            From ObjectsUserBundle:User u
            join u.userRoles r
            where r.id = :roleId and ( u.createdAt = :threeDate or u.createdAt = :sevenDate or u.createdAt = :fifteenDate )
            having cvCount = 0 or educationCount = 0
            ")->setParameters(array(
            'roleId' => $roleActiveId,
            'threeDate' => $threeDate,
            'sevenDate' => $sevenDate,
            'fifteenDate' => $fifteenDate
        ));

        return $query->getResult();
    }

    /**
     * get not active users from 3,7,15 days
     * @author ahmed
     * @param datetime $threeDate
     * @param datetime $sevenDate
     * @param datetime $fifteenDate
     */
    public function getNotActiveUsers($threeDate, $sevenDate, $fifteenDate, $roleActiveId) {
        $query = $this->getEntityManager()->createQuery("
            select u From ObjectsUserBundle:User u
            join u.userRoles r
            where r.id = :roleId and ( u.createdAt = :threeDate or u.createdAt = :sevenDate or u.createdAt = :fifteenDate )
            ")->setParameters(array(
            'roleId' => $roleActiveId,
            'threeDate' => $threeDate,
            'sevenDate' => $sevenDate,
            'fifteenDate' => $fifteenDate
        ));

        return $query->getResult();
    }

    /**
     * get accepted user for new jobs email
     * @author ahmed
     * @return type
     */
    public function getUsersForNewJobs() {
        $query = $this->getEntityManager()
                        ->createQuery('
            SELECT c.id as cvId,u.id as userId,u.email,u.state,u.country
            FROM ObjectsInternJumpBundle:CV c
            JOIN c.user u
            where u.matchingJobEmail = 1 and c.isActive = 1 and u.country = :country
            group by u.email
            ')->setParameter('country', 'US');

        return $query->getResult();
    }

    /**
     * this function used to get worth users
     * @author ahmed
     * @param integer $maxResults
     */
    public function getWorthUsers($maxResults) {
        $query = $this->getEntityManager()->createQuery("
            select u From ObjectsUserBundle:User u
            where u.currentWorth > 1
            order by u.currentWorth desc
        ")->setMaxResults($maxResults);
        return $query->getResult();
    }

    public function getManuallyWorthUsers($maxResults) {
        $query = $this->getEntityManager()->createQuery("
            select u From ObjectsInternJumpBundle:worthPeople u
            order by u.worth desc
        ")->setMaxResults($maxResults);
        return $query->getResult();
    }

    /**
     * implementation of loadUserByUsername for UserProviderInterface
     * @param type $username
     * @return type
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username) {
        $q = $this
                ->createQueryBuilder('u')
                ->select('u, r')
                ->leftJoin('u.userRoles', 'r')
                ->where('u.loginName = :username OR u.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery()
        ;
        $result = $q->getResult();
        if (count($result) > 0) {
            return $result[0];
        }
        $query = $this
                ->createQueryBuilder('u')
                ->select('c, r')
                ->from('Objects\InternJumpBundle\Entity\Company', 'c')
                ->leftJoin('c.companyRoles', 'r')
                ->where('c.loginName = :username OR c.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery()
        ;
        try {
            $user = $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new UsernameNotFoundException(sprintf('Unable to find the specified user: "%s"', $username), null, 0, $e);
        }
        return $user;
    }

    /**
     * implementation of refreshUser for UserProviderInterface
     * @param UserInterface $user
     * @return type
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user) {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * implementation of supportsClass for UserProviderInterface
     * @param type $class
     * @return type
     */
    public function supportsClass($class) {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName()) || 'Objects\InternJumpBundle\Entity\Company' === $class || is_subclass_of($class, 'Objects\InternJumpBundle\Entity\Company');
    }

    /**
     * this function will try to return a user login name that does not exist in our database
     * @author Alshimaa edited by Mahmoud
     * @param string $loginName
     * @return string a valid unique login name
     */
    public function getValidLoginName($loginName) {
        $query = $this->getEntityManager()
                ->createQuery('
                     SELECT max(SUBSTRING(u.loginName, :start)) as offset
                     FROM Objects\UserBundle\Entity\User u
                     WHERE u.loginName like :loginName
                    ');
        $query->setParameter('start', strlen($loginName) + 1);
        $query->setParameter('loginName', $loginName . '%');
        $result = $query->getResult();
        $offset = $result[0]['offset'] + 1;
        return $loginName . $offset;
    }

    /**
     * this function will get the count of the logged in users
     * the UpdateUserLastSeenListener service must be active to return valid number
     * @author Mahmoud
     * @return integer the count of the logged in users
     */
    public function getLoggedUsersCount() {
        $queryString = '
            SELECT count(u.id)
            FROM ObjectsUserBundle:User u
            WHERE u.lastSeen > :time
            ';
        $query = $this->getEntityManager()
                ->createQuery($queryString);
        $dateTime = new \DateTime();
        $dateTime->modify('-5 minute');
        $query->setParameter('time', $dateTime);
        $result = $query->getResult();
        return $result[0][1];
    }

    /**
     * this function will return a user object and his social accounts object if found
     * @author Mahmoud
     * @param integer $userId the user id to get his object
     * @return null|\Objects\UserBundle\Entity\User
     */
    public function getUserWithSocialAccounts($userId) {
        $query = $this->getEntityManager()
                ->createQuery('
                     SELECT u, s
                     FROM Objects\UserBundle\Entity\User u
                     LEFT JOIN u.socialAccounts s
                     WHERE u.id = :userId
                    ');
        $query->setParameter('userId', $userId);
        try {
            $user = $query->getSingleResult();
        } catch (\Exception $e) {
            $user = NULL;
        }
        return $user;
    }

}
