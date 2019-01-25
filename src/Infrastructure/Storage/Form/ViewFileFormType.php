<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Form;

use App\Domain\Storage\Form\ViewFileForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see ViewFileForm
 */
class ViewFileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', TextType::class, [
                'required' => false
            ])
            ->add('filename', TextType::class, [
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => ViewFileForm::class,
            'csrf_protection'      => false,
            'allow_extra_fields'   => true,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
