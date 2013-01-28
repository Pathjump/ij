<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EmploymentHistoryType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('title')
                ->add('isCurrent', 'checkbox', array('required' => FALSE, 'label' => 'Current Position', 'attr' => array('class' => 'employmentIsCurrentJob')))
                ->add('industry', 'entity', array('class' => 'ObjectsInternJumpBundle:CVCategory', 'required' => FALSE, 'attr' => array('class' => 'chzn-select')))
                ->add('description', 'textarea', array('required' => FALSE))
                ->add('startedFrom', 'date', array('attr' => array('class' => 'employmentStartedFrom'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('endedIn', 'date', array('required' => FALSE, 'attr' => array('class' => 'employmentEndedIn'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('companyName')
                ->add('companyUrl', 'url', array('required' => FALSE))
        ;
    }

    public function getName() {
        return 'objects_internjumpbundle_employmenthistorytype';
    }

    public function getDefaultOptions(array $options) {
        $options['data_class'] = '\Objects\InternJumpBundle\Entity\EmploymentHistory';
        return $options;
    }

}
