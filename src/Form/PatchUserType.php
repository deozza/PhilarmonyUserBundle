<?php

namespace Deozza\PhilarmonyUserBundle\Form;

use Deozza\PhilarmonyUserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PatchUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableRoles = $options['availableRoles'];
        $builder
            ->add('username')
            ->add('email')
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password']
            ])
            ->add('active')
            ->add("roles", ChoiceType::class, [
                'choices'=> $availableRoles,
                'multiple'=> true
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class"=>User::class,
            "csrf_protection"=>false,
            "availableRoles" => []
        ]);
    }
}