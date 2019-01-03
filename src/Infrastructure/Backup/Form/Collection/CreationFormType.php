<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Form\Collection;

use App\Domain\Backup\Form\Collection\CreationForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see CreationForm
 */
class CreationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('strategy',          TextType::class)
            ->add('maxBackupsCount',   IntegerType::class)
            ->add('maxCollectionSize', TextType::class)
            ->add('maxOneVersionSize', TextType::class)
            ->add('description',       TextType::class)
            ->add('password',          TextType::class)
            ->add('filename',          TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => CreationForm::class,
            'csrf_protection'      => false,
            'allow_extra_fields'   => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
