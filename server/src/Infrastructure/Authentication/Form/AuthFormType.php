<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Form;

use App\Domain\Authentication\Form\AuthForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roles',   CollectionType::class, [
                'allow_add'    => true,
                'allow_delete' => true
            ])
            ->add('password', TextType::class)
            ->add('about', TextType::class)
            ->add('organization', TextType::class)
            ->add('email', EmailType::class)
            ->add('expires', TextType::class)
            ->add('data',    TokenDetailsFormType::class)
            ->add('id', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => AuthForm::class,
            'csrf_protection'      => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
