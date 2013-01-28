<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CVType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('objective')
                ->add('name')
                ->add('isActive', NULL, array('required' => false, 'attr' => array('class' => 'onoffswitch-checkbox isActiveCv')))
                ->add('categories', NULL, array('required' => false, 'attr' => array('class' => 'chzn-select', 'style' => 'width:637;')))
        ;
    }

    public function getName() {
        return 'objects_internjumpbundle_cvtype';
    }

}
