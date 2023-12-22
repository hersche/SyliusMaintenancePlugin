<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

final class DisableMaintenanceCommand extends Command
{
    protected static $defaultName = 'maintenance:disable';

    public function __construct(
        private ConfigurationFileManager $configurationFileManager,
        private TranslatorInterface $translator,
        private CacheInterface $synoliaMaintenanceCache,
        private MaintenanceConfigurationFactory $configurationFactory,
        private MaintenanceConfigurationExporter $configurationExporter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Disable maintenance plugin')
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'Reset maintenance mode')
            ->setHelp('This command allows you to disable or delete the maintenance.yaml')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->synoliaMaintenanceCache->delete(ConfigurationFileManager::MAINTENANCE_CACHE_KEY);
        $maintenanceConfig = $this->configurationFactory->get();
        $maintenanceConfig->setEnabled(false);
        $this->configurationExporter->export($maintenanceConfig);

        if (true === $input->getOption('clear')) {
            $this->configurationFileManager->deleteMaintenanceFile();
            $output->writeln($this->translator->trans('maintenance.ui.message_reset'));

            return 0;
        }

        $output->writeln($this->translator->trans('maintenance.ui.message_disabled'));

        return 0;
    }
}
