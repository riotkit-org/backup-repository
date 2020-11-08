<?php declare(strict_types=1);

namespace App\Command\Technical;

use App\Infrastructure\Technical\Service\SwaggerDocsProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSwaggerDocsCommand extends Command
{
    public const NAME = 'docs:swagger:json';

    private SwaggerDocsProvider $provider;

    public function __construct(SwaggerDocsProvider $docsProvider)
    {
        $this->provider = $docsProvider;

        parent::__construct(self::NAME);
    }

    public function configure()
    {
        $this->setName(self::NAME);
        $this->setDescription('Print SWAGGER documentation as JSON');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(json_encode($this->provider->provide(), JSON_PRETTY_PRINT));
    }
}
