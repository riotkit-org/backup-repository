<?php declare(strict_types=1);

namespace App\Controller;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Factory\IncomingUserFactory;
use App\Domain\Authentication\Service\Security\TokenExtractorFromHttp;
use App\Domain\Common\Exception\CommonValueException;
use App\Infrastructure\Authentication\Token\TokenTransport;
use App\Infrastructure\Common\Exception\JsonRequestException;
use App\Infrastructure\Common\Service\Http\FormTypeCaster;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

abstract class BaseController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ControllerTrait; // @todo: Remove usage of ControllerTrait due to deprecation

    protected function getParameter(string $name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * @param Request|array $request
     * @param string        $DTOClassName
     * @param callable|null $transformer
     *
     * @return ${DTOClassName}
     *
     * @throws JsonRequestException
     */
    protected function decodeRequestIntoDTO($request, string $DTOClassName, callable $transformer = null)
    {
        if (is_array($request)) {
            $request = FormTypeCaster::recast($request, $DTOClassName);
        }

        $data = $request instanceof Request ? $request->getContent(false) : json_encode($request);

        if ($transformer && strpos(ltrim($data), '{') === 0) {
            $data = json_encode($transformer(json_decode($data, true)));
        }

        try {
            return $this->container->get('serializer')
                ->deserialize($data, $DTOClassName, 'json');

        } catch (\ErrorException | NotEncodableValueException | NotNormalizableValueException $exception) {
            throw JsonRequestException::fromJsonToFormMappingError($exception);
        }
    }

    /**
     * @param string|null $className
     *
     * @return User|\App\Domain\Common\SharedEntity\User|null
     *
     * @throws \App\Domain\Authentication\Exception\AuthenticationException
     * @throws CommonValueException
     */
    protected function getLoggedUser(?string $className = null)
    {
        /**
         * @var TokenTransport $sessionToken
         */
        $sessionToken = $this->get('security.token_storage')->getToken();

        if (!$sessionToken || !$sessionToken->getUser() || !$sessionToken->getUser()->getId()) {
            throw new AccessDeniedHttpException('No active token found');
        }

        if ($className) {
            return $this->get(IncomingUserFactory::class)->createFromString(
                $sessionToken->getUser()->getId(),
                $className,
                $sessionToken->getUser()->getRoles()
            );
        }

        return $sessionToken->getUser();
    }

    protected function getCurrentSessionToken(Request $request): string
    {
        return TokenExtractorFromHttp::extractFromHeaders($request->headers->all());
    }

    protected function getLoggedUserOrAnonymousToken(?string $className = null)
    {
        try {
            return $this->getLoggedUser($className);

        } catch (AccessDeniedHttpException $exception) {
            return User::createAnonymousToken();
        }
    }

    /**
     * @param callable $code
     *
     * @return Response
     */
    protected function withLongExecutionTimeAllowed(callable $code): Response
    {
        $previousValue = (int) ini_get('max_execution_time');
        set_time_limit($this->getLongExecutionTime());

        $return = $code();

        set_time_limit($previousValue);

        return $return;
    }

    private function getLongExecutionTime(): int
    {
        $configured = getenv('LONG_EXECUTION_TIME');

        if (is_numeric($configured)) {
            return (int) $configured;
        }

        return 300;
    }
}
