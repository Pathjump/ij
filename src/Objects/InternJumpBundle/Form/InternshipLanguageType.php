<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class InternshipLanguageType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('spokenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Spoken :'))
                ->add('writtenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Written :'))
                ->add('readFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Read :'))
//                ->add('internship')
                ->add('language', 'entity', array('class' => 'ObjectsInternJumpBundle:Language', 'required' => false))
        ;
    }

    public function getName() {
        return 'objects_internjumpbundle_internshiplanguagetype';
    }

    public function getDefaultOptions(array $options) {
        $options['data_class'] = 'Objects\InternJumpBundle\Entity\InternshipLanguage';
        return $options;
    }

}
