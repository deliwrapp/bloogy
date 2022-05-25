<?php

namespace App\Core\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Core\Entity\File;

class FileFormType extends AbstractType
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
        $userRoles = $this->params->get('userRoles');
        switch ($options['mode']) {
            case 'edition':
                $builder
                    ->add('isPublished', CheckboxType::class, [
                        'required'   => false
                    ])
                    ->add('roleAccess', ChoiceType::class, [
                        'required' => false,
                        'multiple' => false,
                        'expanded' => false,
                        'choices'  => $userRoles,
                    ])
                    ->add('description', TextareaType::class, [
                        'required'   => false
                    ])
                    ->add('submit', SubmitType::class, [
                        'label' => 'Edit',
                    ]);
                break;
            case 'edit-name':
                $builder
                    ->add('name', TextType::class)
                    ->add('submit', SubmitType::class, [
                        'label' => 'Edit name',
                    ]);
                break;
            case 'edit-private':
                $builder
                    ->add('private', CheckboxType::class, [
                        'required'   => false
                    ])
                    ->add('submit', SubmitType::class, [
                        'label' => 'Edit Access',
                    ]);
                break;
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
            'submitBtn' => 'Validate',
            'mode' => 'edition',
            // enable/disable CSRF protection for this form
            'csrf_protection' => true,
            // the name of the hidden HTML field that stores the token
            'csrf_field_name' => '_token',
            // an arbitrary string used to generate the value of the token
            // using a different string for each form improves its security
            'csrf_token_id'   => 'File_item',
        ]);
    }
}