<?php declare(strict_types=1);

namespace App\Infrastructure\Technical\Service;

use App\Domain\Common\Service\Versioning;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class SwaggerDocsProvider
{
    private string      $yamlPath;
    private Environment $twig;
    private Versioning  $versioning;

    public function __construct(string $swaggerYamlPath, Environment $twig, Versioning $versioning)
    {
        $this->yamlPath   = $swaggerYamlPath;
        $this->twig       = $twig;
        $this->versioning = $versioning;
    }

    public function provide(): array
    {
        $rendered = $this->twig->render('swagger.yaml.j2', [
            'version' => $this->versioning->getVersion()
        ]);

        // this is leaved for purpose
        dump($rendered);

        return Yaml::parse($rendered);
    }
}
