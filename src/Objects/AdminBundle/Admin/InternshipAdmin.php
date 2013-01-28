<?php

/**
 * Description of InternshipAdmin
 *
 * @author Mahmoud
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class InternshipAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string 
     */
    protected $baseRouteName = 'internship_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string 
     */
    protected $baseRoutePattern = 'internship';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper 
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('Longitude')
                ->add('Latitude')
                ->add('createdAt')
                ->add('activeFrom')
                ->add('activeTo')
                ->add('active')
                ->add('title')
                //->add('position')
                ->add('description')
                ->add('requirements')
                ->add('company')
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    )
                ))
        ;
    }

    /**
     * this function configure the show action fields
     * @author Ahmed
     * @param ShowMapper $showMapper 
     */
    public function configureShowField(ShowMapper $showMapper) {
        $showMapper
                ->add('id')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('Longitude')
                ->add('Latitude')
                ->add('createdAt')
                ->add('activeFrom')
                ->add('activeTo')
                ->add('active')
                ->add('title')
                //->add('position')
                ->add('description')
                ->add('requirements')
                ->add('company')
                ->add('categories')
        ;
    }

    /**
     * this function configure the list action filters fields
     * @todo add the date filters if sonata project implemented it
     * @author Ahmed
     * @param DatagridMapper $datagridMapper 
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('id')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('Longitude')
                ->add('Latitude')
                ->add('createdAt')
                ->add('activeFrom')
                ->add('activeTo')
                ->add('active')
                ->add('title')
                //->add('position')
                ->add('description')
                ->add('requirements')
                ->add('company')
                ->add('categories')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Mahmoud
     * @param FormMapper $formMapper 
     */
    public function configureFormFields(FormMapper $formMapper) {
        $currentDate = new \DateTime();
        //define the default arrays
        $countries = array();
        $cities = array();
        $states = array();
        //check if we have a new object
        if ($this->getSubject()->getId()) {
            //set the object values
            $countries[$this->getSubject()->getCountry()] = $this->getSubject()->getCountry();
            $cities[$this->getSubject()->getCity()] = $this->getSubject()->getCity();
            $states[$this->getSubject()->getState()] = $this->getSubject()->getState();
        }
        $formMapper
                ->with('Required Fields')
                ->add('company', 'sonata_type_model', array('required' => true), array('edit' => 'list', 'admin_code' => 'company_list_admin'))
                ->add('zipcode','text',array('attr' => array('class' => 'zipcode')))
                ->add('Longitude','text',array('attr' => array('class' => 'longitude')))
                ->add('Latitude','text',array('attr' => array('class' => 'latitude')))
                ->add('country', 'choice', array(
                    'choices' => $countries,
                    'attr' => array('class' => 'chosen countrySelect')
                ))
                ->add('city', 'choice', array(
                    'choices' => $cities,
                    'attr' => array('class' => 'chosen citySelect')
                ))
                ->add('state', 'choice', array(
                    'choices' => $states,
                    'attr' => array('class' => 'chosen stateSelect'),
                    'required' => false
                ))
                ->add('address')
                ->add('activeFrom', 'date', array('years' => range($currentDate->format('Y'), $currentDate->format('Y') + 5)))
                ->add('activeTo', 'date', array('years' => range($currentDate->format('Y'), $currentDate->format('Y') + 5)))
                ->add('active', NULL, array('required' => false))
                ->add('categories', 'sonata_type_model', array('attr' => array('class' => 'chosen')))
                ->add('title')
                //->add('position')
                ->add('description')
                ->add('requirements')
                ->end()
        ;
    }

}

?>
