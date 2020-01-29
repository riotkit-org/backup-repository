<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\DependencyInjection;

use App\Infrastructure\Authentication\Token\TokenTransport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class TokenResolver implements ParamConverterInterface
{
    /**
     * @var Security
     */
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $request->attributes->set($configuration->getName(), $this->security->getToken());
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === TokenTransport::class;
    }
}
