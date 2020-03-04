<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Form;

use App\Domain\Storage\Form\UploadByPostForm;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadByPostFormType extends UploadFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('fileName', TextType::class, [
                'required' => true
            ])
            ->add('stripInvalidCharacters', ChoiceType::class, [
                'choices' => [
                    'true', 'false', '1', '0', 'TRUE', 'FALSE'
                ]
            ]);

        $builder->get('stripInvalidCharacters')
            ->addModelTransformer(new CallbackTransformer(
                static function ($value) {
                    return (string) $value;
                },
                static function ($value) {
                    // default value
                    if ($value === null || $value === '') {
                        return true;
                    }

                    if (\in_array($value, ['true', '1', 'TRUE'])) {
                        return true;
                    }

                    return false;
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => UploadByPostForm::class,
            'csrf_protection'      => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}',
            'allow_extra_fields'   => true // _token
        ]);
    }
}
