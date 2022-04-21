<?php

namespace App\Security\Form;

use App\Security\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AdminUserType extends AbstractType
{
    /** @var ParameterBagInterface */
    private $params;

    public function __construct(
        ParameterBagInterface $params
    )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $localesList = $this->params->get('appLocalesList');
        $builder
            ->add('email')
            ->add('username')
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                  'User' => 'ROLE_USER',
                  'Moderator' => 'ROLE_MODERATOR',
                  'Editor' => 'ROLE_EDITOR',
                  'Admin' => 'ROLE_ADMIN',
                  'Super Admin' => 'ROLE_SUPER_ADMIN',
                ],
            ])
            ->add('locale', ChoiceType::class, [
                'choices'  => array_flip($localesList)
            ])
            ->add('isVerified')
            ->add('isRestricted')
        ;

        if ($options['mode'] == "create") {
            $builder->add('password', PasswordType::class, ['label' => 'Password']);
        }

        // roles field data transformer 
        $builder->get('roles')
        ->addModelTransformer(new CallbackTransformer(
            function ($rolesArray) {
                // transform the array to a string
                return count($rolesArray)? $rolesArray[0]: null;
            },
            function ($rolesString) {
                // transform the string back to an array
                return [$rolesString];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'mode' => 'edit',
        ]);
    }
}
