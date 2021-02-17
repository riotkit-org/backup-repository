<?php declare(strict_types=1);

namespace App\Infrastructure\Technical\TwigExtension;

use App\Infrastructure\Technical\Service\SwaggerDocsProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class IndentByTwigExtension extends AbstractExtension
{
    private SwaggerDocsProvider $provider;

    public function __construct(SwaggerDocsProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('indent_by', [$this, 'indentByFunction'])
        ];
    }

    public function indentByFunction(string $text, string $indentBy): string
    {
        $lines = explode("\n", $text);
        $num = 0;

        foreach ($lines as &$line) {
            $num++;

            // do not indent first line
            if ($num === 1) {
                continue;
            }

            $line = $indentBy . $line;
        }

        return implode("\n", $lines);
    }
}
