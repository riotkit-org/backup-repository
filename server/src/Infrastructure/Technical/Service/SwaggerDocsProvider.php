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
    private bool        $isDebug;

    public function __construct(string $swaggerYamlPath, Environment $twig, Versioning $versioning, bool $isDebug)
    {
        $this->yamlPath   = $swaggerYamlPath;
        $this->twig       = $twig;
        $this->versioning = $versioning;
        $this->isDebug    = $isDebug;
    }

    public function provide(): array
    {
        $rendered = $this->twig->render('swagger.yaml.j2', [
            'version' => $this->versioning->getVersion()
        ]);

        if ($this->isDebug) {
            $fp = fopen(__DIR__ . '/../../../../var/swagger.yaml', 'w');
            fwrite($fp, $rendered);
            fclose($fp);
        }

        return Yaml::parse($rendered);
    }
}
