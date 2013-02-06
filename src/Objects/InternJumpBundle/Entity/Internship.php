<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Locale\Locale;

/**
 * Objects\InternJumpBundle\Entity\Internship
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\InternshipRepository")
 */
class Internship {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the company of the internship
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="internships")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $categories
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\CVCategory")
     * @ORM\JoinTable(name="internship_category",
     *     joinColumns={@ORM\JoinColumn(name="internship_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $categories;

    /**
     * the interviews of the internship
     * @var \Doctrine\Common\Collections\ArrayCollection $interviews
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Interview", mappedBy="internship", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $interviews;

    /**
     * the internships of the users
     * @var \Doctrine\Common\Collections\ArrayCollection $usersInternships
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\UserInternship", mappedBy="internship", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $usersInternships;

    /**
     * the tasks of the internship
     * @var \Doctrine\Common\Collections\ArrayCollection $tasks
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Task", mappedBy="internship", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $tasks;

    /**
     * @var decimal $Latitude
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="Latitude", type="decimal", precision=18, scale=12)
     */
    private $Latitude;

    /**
     * @var decimal $Longitude
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="Longitude", type="decimal", precision=18, scale=12)
     */
    private $Longitude;

    /**
     * @var string $zipcode
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="zipcode", type="string", length=255)
     */
    private $zipcode;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    /**
     * @var date $activeFrom
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @Assert\Date
     * @ORM\Column(name="activeFrom", type="date")
     */
    private $activeFrom;

    /**
     * @var date $activeTo
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @Assert\Date
     * @ORM\Column(name="activeTo", type="date")
     */
    private $activeTo;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = TRUE;

    /**
     * @var string $title
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $description
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var text $requirements
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="requirements", type="text")
     */
    private $requirements;

    /**
     * @var string $country
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string $city
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;

    /**
     * @var string $state
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     */
    private $state;

    /**
     * @var text $address
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="address", type="text")
     */
    private $address;

    public function __toString() {
        return $this->title;
    }

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->activeFrom = new \DateTime();
        $this->tasks = new ArrayCollection();
        $this->interviews = new ArrayCollection();
        $this->usersInternships = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get createdAt
     *
     * @return date
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set activeFrom
     *
     * @param date $activeFrom
     */
    public function setActiveFrom($activeFrom) {
        $this->activeFrom = $activeFrom;
    }

    /**
     * Get activeFrom
     *
     * @return date
     */
    public function getActiveFrom() {
        return $this->activeFrom;
    }

    /**
     * Set activeTo
     *
     * @param date $activeTo
     */
    public function setActiveTo($activeTo) {
        $this->activeTo = $activeTo;
    }

    /**
     * Get activeTo
     *
     * @return date
     */
    public function getActiveTo() {
        return $this->activeTo;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active) {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set country
     *
     * @param string $country
     */
    public function setCountry($country) {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param string $city
     */
    public function setCity($city) {
        $this->city = $city;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     */
    public function setState($state) {
        $this->state = $state;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Set address
     *
     * @param text $address
     */
    public function setAddress($address) {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return text
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Set company
     *
     * @param Objects\InternJumpBundle\Entity\Company $company
     */
    public function setCompany(\Objects\InternJumpBundle\Entity\Company $company) {
        $this->company = $company;
    }

    /**
     * Get company
     *
     * @return Objects\InternJumpBundle\Entity\Company
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * Add interviews
     *
     * @param Objects\InternJumpBundle\Entity\Interview $interviews
     */
    public function addInterview(\Objects\InternJumpBundle\Entity\Interview $interviews) {
        $this->interviews[] = $interviews;
    }

    /**
     * Get interviews
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getInterviews() {
        return $this->interviews;
    }

    /**
     * Add usersInternships
     *
     * @param Objects\InternJumpBundle\Entity\UserInternship $usersInternships
     */
    public function addUserInternship(\Objects\InternJumpBundle\Entity\UserInternship $usersInternships) {
        $this->usersInternships[] = $usersInternships;
    }

    /**
     * Get usersInternships
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getUsersInternships() {
        return $this->usersInternships;
    }

    /**
     * Add tasks
     *
     * @param Objects\InternJumpBundle\Entity\Task $tasks
     */
    public function addTask(\Objects\InternJumpBundle\Entity\Task $tasks) {
        $this->tasks[] = $tasks;
    }

    /**
     * Get tasks
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTasks() {
        return $this->tasks;
    }

    /**
     * this function will check if the internship time is valid
     * @Assert\True(groups={"newInternship","editInternship"},message = "The active to date must be greater than the active from date")
     */
    public function isActiveToCorrect() {
        if ($this->getActiveTo() > $this->getActiveFrom()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * this function will check if the internship time is valid
     * @Assert\True(groups={"newInternship"},message = "The active from date must be greater than or equal the current date")
     */
    public function isFromDateCorrect() {
        $nowDate = new \DateTime('today');
        if ($this->getActiveFrom() >= $nowDate) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * this function will check if the internship has at least one category
     * @Assert\True(message = "The internship must have at least one category")
     */
    public function isCategoriesCorrect() {
        if (count($this->getCategories()) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Set Latitude
     *
     * @param decimal $latitude
     */
    public function setLatitude($latitude) {
        $this->Latitude = $latitude;
    }

    /**
     * Get Latitude
     *
     * @return decimal
     */
    public function getLatitude() {
        return $this->Latitude;
    }

    /**
     * Set Longitude
     *
     * @param decimal $longitude
     */
    public function setLongitude($longitude) {
        $this->Longitude = $longitude;
    }

    /**
     * Get Longitude
     *
     * @return decimal
     */
    public function getLongitude() {
        return $this->Longitude;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     */
    public function setZipcode($zipcode) {
        $this->zipcode = $zipcode;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode() {
        return $this->zipcode;
    }

    /**
     * Set requirements
     *
     * @param text $requirements
     */
    public function setRequirements($requirements) {
        $this->requirements = $requirements;
    }

    /**
     * Get requirements
     *
     * @return text
     */
    public function getRequirements() {
        return $this->requirements;
    }

    /**
     * Add categories
     *
     * @param Objects\InternJumpBundle\Entity\CVCategory $categories
     */
    public function addCVCategory(\Objects\InternJumpBundle\Entity\CVCategory $categories) {
        $this->categories[] = $categories;
    }

    /**
     * Get categories
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     * Set categories
     *
     * @param Doctrine\Common\Collections\Collection $categories
     */
    public function setCategories($categories) {
        $this->categories = $categories;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    /**
     * this function will return the user country name
     * @return NULL|string the country name
     */
    public function getCountryName() {
        //check if we have a country code
        if ($this->country) {
            //return the country name
            return Locale::getDisplayRegion('en_' . $this->country);
        }
        return NULL;
    }

}
