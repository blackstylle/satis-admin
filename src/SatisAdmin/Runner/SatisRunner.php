<?php

namespace SatisAdmin\Runner;

use Monolog\Logger;
use SatisAdmin\Model\ModelManager;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class SatisRunner implements RunnerInterface
{
    /**
     * @var ModelManager
     */
    protected $manager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $outputDir;

    /**
     * @var string
     */
    protected $binDir;

    /**
     * @param ModelManager $manager
     * @param Logger       $logger
     * @param string       $outputDir
     * @param string       $binDir
     */
    public function __construct(ModelManager $manager, Logger $logger, $outputDir, $binDir)
    {
        $this->manager   = $manager;
        $this->logger    = $logger;
        $this->outputDir = $outputDir;
        $this->binDir    = $binDir;
    }

    public function run()
    {
        $configFile = tempnam(sys_get_temp_dir(), 'satis-admin');
        file_put_contents($configFile, $this->manager->getJson());
        $process = ProcessBuilder::create(
            [
                'php',
                $this->binDir.'/satis',
                'build',
                $configFile,
                $this->outputDir
            ]
        )->getProcess();

        $this->logger->addInfo('Building config...', ['command-line' => $process->getCommandLine()]);
        if (0 === $process->run()) {
            $this->logger->addInfo('Config built.');
        } else {
            $this->logger->addError(
                'Config not build',
                [
                    'stdout' => $process->getOutput(),
                    'stderr' => $process->getErrorOutput()
                ]
            );
        }
    }
}
