<?php declare(strict_types=1);

namespace App\Controller;

use App\Domain\Common\ValueObject\BaseUrl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseController extends Controller
{
    protected function submitFormFromJsonRequest(Request $request, $formObject, string $formType): FormInterface
    {
        $arrayForm = \json_decode($request->getContent(), true);

        if (!\is_array($arrayForm)) {
            throw new \InvalidArgumentException('Missing content');
        }

        $infrastructureForm = $this->createForm($formType, $formObject);
        $infrastructureForm->submit($arrayForm);

        return $infrastructureForm;
    }

    protected function submitFormFromRequestQuery(Request $request, $formObject, string $formType): FormInterface
    {
        $infrastructureForm = $this->createForm($formType, $formObject);
        $infrastructureForm->submit($request->query->all());

        return $infrastructureForm;
    }

    protected function createValidationErrorResponse(FormInterface $form): JsonResponse
    {
        return new JsonResponse(
            $this->collectErrorsForForm($form, 'form', []),
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    protected function createBaseUrl(Request $request): BaseUrl
    {
        return new BaseUrl($request->isSecure(), $request->getHttpHost());
    }

    private function collectErrorsForForm(FormInterface $form, string $inputName, array $errors = []): array
    {
        $errorsForField = [];

        foreach ($form->getErrors() as $error) {
            $errorsForField[] = $error->getMessage();
        }

        if (\count($errorsForField) > 0) {
            $errors[$inputName] = $errorsForField;
        }

        // children fields
        if ($form->count()) {
            $compound = [];

            foreach ($form as $child) {
                $compound[] = $this->collectErrorsForForm($child, $inputName . '.' . $child->getName(), $errors);
            }

            if ($compound) {
                $errors = array_merge($errors, ...$compound);
            }
        }

        return $errors;
    }
}
