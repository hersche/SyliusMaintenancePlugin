<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

final class EnableMaintenanceCommand extends Command
{
    protected static $defaultName = 'maintenance:enable';

    private ConfigurationFileManager $fileManager;

    private TranslatorInterface $translator;

    public function __construct(ConfigurationFileManager $fileManager, TranslatorInterface $translator)
    {
        $this->fileManager = $fileManager;
        $this->translator = $translator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Turn your website under maintenance.')
            ->addArgument('ips_address', InputArgument::IS_ARRAY, 'Add ips addresses (separate multiple ips with a space)')
            ->setHelp('This command allows you to create the maintenance.yaml and also allows you to put the ips into this file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->translator->trans($this->fileManager->createFile(ConfigurationFileManager::MAINTENANCE_FILE)));

        /** @var array $ipsAddress */
        $ipsAddress = $input->getArgument('ips_address');

        if (0 < \count($ipsAddress)) {
            $output->writeln($this->translator->trans($this->fileManager->putIpsIntoFile($ipsAddress, ConfigurationFileManager::MAINTENANCE_FILE)));
        }

        return 0;
    }
}
