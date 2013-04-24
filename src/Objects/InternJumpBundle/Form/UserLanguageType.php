<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserLanguageType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('spokenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Spoken :', 'attr' => array('class' => 'lngopt')))
                ->add('writtenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Written :', 'attr' => array('class' => 'lngopt')))
                ->add('readFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Read :', 'attr' => array('class' => 'lngopt')))
//            ->add('user')
                ->add('language', 'entity', array('class' => 'ObjectsInternJumpBundle:Language', 'required' => false, 'attr' => array('data-placeholder' => "Choose a Language ...", 'class' => 'chzn-select')))
        ;
    }

    public function getName() {
        return 'objects_internjumpbundle_userlanguagetype';
    }

    public function getDefaultOptions(array $options) {
        $options['data_class'] = '\Objects\InternJumpBundle\Entity\UserLanguage';
        return $options;
    }

}