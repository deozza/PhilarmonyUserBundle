<?php
namespace Deozza\PhilarmonyUserBundle\Service;

use Deozza\PhilarmonyUserBundle\Form\RegistrationType;
use Symfony\Component\Form\FormFactoryInterface;

class ProcessUserForm
{
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->form = $formFactory;
    }

    public function buildUserForm($class, $userClass, $form)
    {
        $user = new \ReflectionClass($userClass);
        $properties = $user->getProperties();
        foreach($properties as $property)
        {
            if($property->getName() != "id" && $property->getName() != "uuid")
            {
                $class->{$property->getName()} = null;
                $form->add($property->getName());
            }
        }
        return ["form"=>$form, "class"=>$class];
    }

    public function saveUserForm($class, $user)
    {
        foreach($class as $property=>$value)
        {
            $setter = "set".ucfirst($property);
            $user->$setter($value);
        }
        
        return $user;
    }
}