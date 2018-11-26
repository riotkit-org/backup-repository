<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Form;

use App\Domain\Storage\Form\FilesListingForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see FilesListingForm
 */
class FilesListingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('page', NumberType::class)
            ->add('limit', NumberType::class)
            ->add('searchQuery', TextType::class)
            ->add('tags', CollectionType::class, [
                'allow_add'    => true,
                'allow_delete' => true
            ])
            ->add('mimes', CollectionType::class, [
                'allow_add'    => true,
                'allow_delete' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => FilesListingForm::class,
            'csrf_protection'      => false,
            'allow_extra_fields'   => true,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
