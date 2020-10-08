<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Form;

use App\Domain\Authentication\Form\RestrictionsForm;
use App\Domain\Common\SharedEntity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(User::FIELD_TAGS, CollectionType::class, [
                'allow_add'    => true,
                'allow_delete' => true
            ])
            ->add(User::FIELD_MAX_ALLOWED_FILE_SIZE, IntegerType::class)
            ->add(User::FIELD_ALLOWED_IPS, CollectionType::class, [
                'allow_add'    => true,
                'allow_delete' => true
            ])
            ->add(User::FIELD_ALLOWED_UAS, CollectionType::class, [
                'allow_add'    => true,
                'allow_delete' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => RestrictionsForm::class,
            'csrf_protection'      => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
