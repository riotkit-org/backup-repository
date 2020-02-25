<?php declare(strict_types=1);

namespace App\Command\Storage;

use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Context\CachingContext;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\ViewFileForm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reads a file from the storage
 */
class ReadFileCommand extends Command
{
    public const NAME = 'storage:file:read';

    private ViewFileHandler $handler;
    private SecurityContextFactory $contextFactory;

    public function __construct(ViewFileHandler $handler, SecurityContextFactory $contextFactory)
    {
        $this->handler        = $handler;
        $this->contextFactory = $contextFactory;

        parent::__construct(self::NAME);
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Read the file from storage and print out to the stdout')
            ->addArgument('filename', InputOption::VALUE_REQUIRED,'Filename');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $form = new ViewFileForm();
        $form->filename   = $input->getArgument('filename');
        $form->bytesRange = '';

        $cachingContext = new CachingContext('');
        $securityContext = $this->contextFactory->createReadContextInShell();

        $response = $this->handler->handle($form, $securityContext, $cachingContext);
        $callback = $response->getResponseCallback();

        if (!$callback) {
            throw new \Exception('Cannot read the file, maybe the id is not valid?');
        }

        $callback();

        return 0;
    }
}
