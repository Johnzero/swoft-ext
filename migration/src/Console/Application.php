<?php

namespace Swoft\Migrations\Console;

use Swoft\App;
use Swoft\Bean\BeanFactory;
use Swoft\Core\Config;
use Swoft\Migrations\Console\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as Console;

class Application extends Console
{
    /**
     * Class Constructor.
     *
     * Initialize the Phinx console application.
     *
     * @param string $version The Application Version
     */
    public function __construct()
    {
        parent::__construct('LLDCA', '0.1.0-dev');

        $container = BeanFactory::getContainer();

        if (!$container->hasBean('config')) {
            $container->addDefinitions([
                'config' => [
                    'class'      => Config::class,
                    'properties' => value(function () {
                        $config     = new Config();
                        $properties = [];
                        $dir        = App::getAlias('@properties');
                        if (is_readable($dir)) {
                            $config->load($dir);
                            $properties = $config->toArray();
                        }

                        return $properties;
                    }),
                ]
            ]);
        }

        $this->addCommands([
            new Command\Init(),
            new Command\Create(),
            new Command\Migrate(),
            new Command\Rollback(),
            new Command\Status(),
            new Command\Breakpoint(),
            new Command\Test(),
            new Command\SeedCreate(),
            new Command\SeedRun(),
        ]);
    }

    /**
     * 非命令行下调用
     *
     * @param string $command
     * @param array $input
     * @return string
     */
    public static function call(string $command, ...$input)
    {
        $output = new BufferedOutput();
        array_unshift($input, $command);
        array_unshift($input, '');

        (new static)->doRun(new ArgvInput($input), $output);

        return $output->fetch();
    }

    /**
     * Runs the current application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input An Input instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output An Output instance
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // always show the version information except when the user invokes the help
        // command as that already does it
        if ($input->hasParameterOption(['--help', '-h']) === false && $input->getFirstArgument() !== null) {
            $output->writeln($this->getLongVersion());
            $output->writeln('');
        }

        return parent::doRun($input, $output);
    }

}
