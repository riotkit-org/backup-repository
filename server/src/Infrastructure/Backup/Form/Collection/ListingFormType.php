<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Form\Collection;

use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Form\Collection\ListingForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see CreationForm
 */
class ListingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchQuery', TextType::class)
            ->add('allowedTokens', CollectionType::class, [
                'entry_type'   => TextType::class,
                'allow_add'    => true,
                'allow_delete' => true
            ])
            ->add('createdFrom', DateType::class, ['widget' => 'single_text'])
            ->add('createdTo',   DateType::class, ['widget' => 'single_text'])
            ->add('page',      IntegerType::class)
            ->add('limit',     IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => ListingForm::class,
            'csrf_protection'      => false,
            'allow_extra_fields'   => true, // eg. _token must be allowed for a GET type endpoint
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
