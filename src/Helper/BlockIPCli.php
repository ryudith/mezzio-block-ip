<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Helper;

use Exception;
use Ryudith\MezzioBlockIp\Storage\FileSystemStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Blog IP CLI helper
 */
class BlockIPCli extends Command
{
    /**
     * Reference to input instance.
     * 
     * @var InputInterface $input
     */
    private InputInterface $input;

    /**
     * Reference to outout instance.
     * 
     * @var OutputInterface $output
     */
    private OutputInterface $output;

    /**
     * Detail message about process result.
     * 
     * @var string $processResultMessage
     */
    private ?string $processResultMessage = null;

    /**
     * 
     */
    public function __construct (
        private array $config,
        private FileSystemStorage $storage
    )
    {
        parent::__construct();
    }

    /**
     * Configure argument for CLI command.
     * 
     * @return void
     */
    protected function configure() : void
    {
        $this->addArgument('cmd', InputArgument::OPTIONAL, 'Command to execute');
        $this->addArgument('ip', InputArgument::OPTIONAL, 'IP to add blacklist or whitelist');
    }

    /**
     * Execute CLI helper.
     * 
     * @param InputInterface $input CLI input reference.
     * @param OutputInterface $output CLI output reference.
     * @return int Result of process.
     */
    public function execute (InputInterface $input, OutputInterface $output) : int 
    {
        $this->input = $input;
        $this->output = $output;

        $cmd = strtolower($input->getArgument('cmd') ?? '');
        if ($cmd === '' || $cmd === 'help')
        {
            return $this->help();
        }

        $ip = $input->getArgument('ip');
        if ($ip === null || ! filter_var($ip, FILTER_VALIDATE_IP))
        {
            throw new Exception('No IP value input!');
            return Command::FAILURE;
        }

        $this->storage->setIP($ip);
        $result = match ($cmd)
        {
            'blacklist:add' => $this->addBlacklist(),
            'blacklist:delete' => $this->deleteBlacklist(),
            'whitelist:add' => $this->addWhitelist(),
            'whitelist:delete' => $this->deleteWhitelist(),
            default => $this->help()
        };

        if ($result == Command::FAILURE)
        {
            throw new Exception($this->processResultMessage);
        }
        else if ($this->processResultMessage !== null)
        {
            $this->output->writeln($this->processResultMessage);
        }

        return $result;
    }

    /**
     * Show help information.
     * 
     * @return int Always return 0 or Command::SUCCESS
     */
    private function help () : int
    {
        $this->output->writeln("\nDefault argument is 'help'.\n\n".
            "help             -> Show this help information.\n".
            "blacklist:add    -> Add IP to blacklist.\n".
            "blacklist:delete -> Delete IP from blacklist.\n".
            "whitelist:add    -> Add IP to whitelist.\n".
            "whitelist:delete -> Delete IP from whitelist.\n\n".
            "Example usage : \n\n".
            "  $ [laminas-registered-command] blacklist:add \"192.168.11.12\"\n");
        
        return Command::SUCCESS;
    }

    /**
     * Add IP to blacklist if not exists yet.
     * 
     * @return int Add blacklist process result.
     */
    private function addBlacklist () : int
    {
        if (! $this->storage->isBlacklist() && $this->storage->createBlacklistRecord()) 
        {
            $this->processResultMessage = 'add ip to blacklist success.';
            return Command::SUCCESS;
        }

        $this->processResultMessage = 'add ip to blacklist fail.';
        return Command::FAILURE;
    }

    /**
     * Delete IP from blacklist.
     * 
     * @return bool Delete blacklist process result.
     */
    private function deleteBlacklist () : int 
    {
        if ($this->storage->isBlacklist() && $this->storage->deleteBlacklistRecord()) 
        {
            $this->processResultMessage = 'delete ip from blacklist success.';
            return Command::SUCCESS;
        }

        $this->processResultMessage = 'delete ip from blacklist fail.';
        return Command::FAILURE;
    }

    /**
     * Add IP to whitelist if not exists yet.
     * 
     * @return bool Add whitelist process result.
     */
    private function addWhitelist () : int
    {
        if (! $this->storage->isWhitelist() && $this->storage->createWhitelistRecord()) 
        {
            $this->processResultMessage = 'add ip to whitelist success.';
            return Command::SUCCESS;
        }

        $this->processResultMessage = 'add ip to whitelist fail.';
        return Command::FAILURE;
    }

    /**
     * Delete IP from whitelist.
     * 
     * @return bool Delete whitelist process result.
     */
    private function deleteWhitelist () : int
    {
        if ($this->storage->isWhitelist() && $this->storage->deleteWhitelistRecord()) 
        {
            $this->processResultMessage = 'delete ip from whitelist success.';
            return Command::SUCCESS;
        }

        $this->processResultMessage = 'delete ip from whitelist fail.';
        return Command::FAILURE;
    }
}