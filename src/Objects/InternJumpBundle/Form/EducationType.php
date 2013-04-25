<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EducationType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $years = array_reverse(range(1980, date('Y') + 7));
        $choices = array();
        foreach ($years as $year) {
            $choices[$year] = $year;
        }
        $builder
                ->add('schoolName')
                ->add('underGraduate', 'choice', array('choices' => array('UnderGraduate', 'Graduate'), 'expanded' => true, 'required' => FALSE))
                ->add('major', NULL, array('required' => FALSE))
                ->add('minor', NULL, array('required' => FALSE))
                ->add('majorGPA', 'number', array('precision' => 2, 'required' => FALSE))
                ->add('cumulativeGPA', 'number', array('precision' => 2, 'required' => FALSE))
                ->add('startDate', 'choice', array('required' => FALSE, 'empty_data' => null, 'choices' => $choices, 'attr' => array('class' => 'selectbox')))
                ->add('endDate', 'choice', array('required' => FALSE, 'empty_data' => null, 'choices' => $choices, 'attr' => array('class' => 'selectbox')))
                ->add('extracurricularActivity', 'textarea', array('required' => FALSE))
                ->add('relevantCourseworkTaken', 'textarea', array('required' => FALSE))
                ->add('graduateDegree', NULL, array('required' => FALSE))
                ->add('underGraduateDegree', NULL, array('required' => FALSE))
        ;
    }

    public function getName() {
        return 'objects_internjumpbundle_educationtype';
    }

    public function getDefaultOptions(array $options) {
        $options['data_class'] = '\Objects\InternJumpBundle\Entity\Education';
        return $options;
    }

}
