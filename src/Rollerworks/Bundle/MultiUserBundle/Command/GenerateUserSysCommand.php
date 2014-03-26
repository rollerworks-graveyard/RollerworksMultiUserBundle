<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Command;

use Rollerworks\Bundle\MultiUserBundle\Generator\UserBundleGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class GenerateUserSysCommand extends GenerateBundleCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('rollerworks:multi-user:generate:usersys');

        $definition = $this->getDefinition();
        $definition->addOption(new InputOption('db-driver', '', InputOption::VALUE_REQUIRED, 'DB-driver to use'));

        $this
            ->setHelp(<<<EOT
The <info>rollerworks:multi-user:generate:usersys</info> command helps you generates new user-system bundles.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--namespace</comment> is the only one needed if you follow the
conventions):

<info>php app/console rollerworks:multi-user:generate:usersys --namespace=Acme/UserBundle</info>

Note that you can use <comment>/</comment> instead of <comment>\\ </comment>for the namespace delimiter to avoid any
problem.

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options:

<info>php app/console rollerworks:multi-user:generate:usersys --namespace=Acme/UserBundle --dir=src [--db-driver=...] [--bundle-name=...] --no-interaction</info>

Note that the bundle namespace must end with "Bundle".
EOT
            );
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        foreach (array('namespace', 'dir') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $namespace = Validators::validateBundleNamespace($input->getOption('namespace'));
        if (!$bundle = $input->getOption('bundle-name')) {
            $bundle = strtr($namespace, array('\\' => ''));
        }
        $bundle = Validators::validateBundleName($bundle);
        $dir = Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace);

        if (null === $input->getOption('format')) {
            $input->setOption('format', 'xml');
        }
        $format = Validators::validateFormat($input->getOption('format'));
        $structure = $input->getOption('structure');

        if (null === $input->getOption('db-driver')) {
            $input->setOption('db-driver', 'orm');
        }
        $dbDriver = Validators::validateDbDriver($input->getOption('db-driver'));

        $dialog->writeSection($output, 'Bundle generation');

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $generator = $this->getGenerator();
        $generator->generate($namespace, $bundle, $dir, $format, $structure, $dbDriver);

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // check that the namespace is already autoloaded
        $runner($this->checkAutoloader($output, $namespace, $bundle, $dir));

        // register the bundle in the Kernel class
        $runner($this->updateKernel($dialog, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        $dialog->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the RollerworksMultiUser UserSys-bundle generator');

        // namespace
        $namespace = null;
        try {
            $namespace = $input->getOption('namespace') ? Validators::validateBundleNamespace($input->getOption('namespace')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $namespace) {
            $output->writeln(array(
                '',
                'Your user-systems code must be written in <comment>bundles</comment>. This command helps',
                'you generate them easily.',
                '',
                'Each user-system bundle is hosted under a namespace (like <comment>Acme/Bundle/UserBundle</comment>).',
                'The namespace should begin with a "vendor" name like your company name, your',
                'project name, or your client name, followed by one or more optional category',
                'sub-namespaces, and it should end with the bundle name itself',
                '(which must have <comment>Bundle</comment> as a suffix).',
                '',
                'See http://symfony.com/doc/current/cookbook/bundles/best_practices.html#index-1 for more',
                'details on bundle naming conventions.',
                '',
                'Use <comment>/</comment> instead of <comment>\\ </comment> for the namespace delimiter to avoid any problem.',
                '',
            ));

            $namespace = $dialog->askAndValidate($output, $dialog->getQuestion('Bundle namespace', $input->getOption('namespace')), array('Rollerworks\Bundle\MultiUserBundle\Command\Validators', 'validateBundleNamespace'), false, $input->getOption('namespace'));
            $input->setOption('namespace', $namespace);
        }

        // bundle name
        $bundle = null;
        try {
            $bundle = $input->getOption('bundle-name') ? Validators::validateBundleName($input->getOption('bundle-name')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $bundle) {
            $bundle = strtr($namespace, array('\\Bundle\\' => '', '\\' => ''));

            $output->writeln(array(
                '',
                'In your code, a bundle is often referenced by its name. It can be the',
                'concatenation of all namespace parts but it\'s really up to you to come',
                'up with a unique name (a good practice is to start with the vendor name).',
                'Based on the namespace, we suggest <comment>'.$bundle.'</comment>.',
                '',
            ));
            $bundle = $dialog->askAndValidate($output, $dialog->getQuestion('Bundle name', $bundle), array('Rollerworks\Bundle\MultiUserBundle\Command\Validators', 'validateBundleName'), false, $bundle);
            $input->setOption('bundle-name', $bundle);
        }

        // target dir
        $dir = null;
        try {
            $dir = $input->getOption('dir') ? Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $dir) {
            $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $output->writeln(array(
                '',
                'The bundle can be generated anywhere. The suggested default directory uses',
                'the standard conventions.',
                '',
            ));
            $dir = $dialog->askAndValidate($output, $dialog->getQuestion('Target directory', $dir), function ($dir) use ($bundle, $namespace) { return Validators::validateTargetDir($dir, $bundle, $namespace); }, false, $dir);
            $input->setOption('dir', $dir);
        }

        // format
        $format = null;
        try {
            $format = $input->getOption('format') ? Validators::validateFormat($input->getOption('format')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $format) {
            $output->writeln(array(
                '',
                'Determine the format to use for the generated configuration, note that annotation is not supported.',
                '',
            ));
            $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml or php)', $input->getOption('format')), array('Rollerworks\Bundle\MultiUserBundle\Command\Validators', 'validateFormat'), false, $input->getOption('format'));
            $input->setOption('format', $format);
        }

        // db-driver
        $dbDriver = null;
        try {
            $dbDriver = $input->getOption('db-driver') ?: null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $dbDriver) {
            $output->writeln(array(
                '',
                'For the user-system to work you need to configure a db-driver.',
                '',
            ));
            $dbDriver = $dialog->askAndValidate($output, $dialog->getQuestion('Db-driver (orm, mongodb, couchdb or custom)', $input->getOption('db-driver')), array('Rollerworks\Bundle\MultiUserBundle\Command\Validators', 'validateDbDriver'), false, $input->getOption('db-driver'));
            $input->setOption('db-driver', $dbDriver);
        }

        // optional files to generate
        $output->writeln(array(
            '',
            'To help you get started faster, the command can generate some',
            'code snippets for you.',
            '',
        ));

        $structure = $input->getOption('structure');
        if (!$structure && $dialog->askConfirmation($output, $dialog->getQuestion('Do you want to generate the whole directory structure', 'no', '?'), false)) {
            $structure = true;
        }
        $input->setOption('structure', $structure);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s\\%s</info>\" bundle\nin \"<info>%s</info>\" using the \"<info>%s</info>\" format with db-driver \"%s\".", $namespace, $bundle, $dir, $format, $dbDriver),
            '',
        ));
    }

    protected function createGenerator()
    {
        return new UserBundleGenerator($this->getContainer()->get('filesystem'));
    }

    /**
     * add this bundle skeleton dirs to the beginning of the parent skeletonDirs array
     *
     * @param BundleInterface $bundle
     *
     * @return string[]
     */
    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $baseSkeletonDirs = parent::getSkeletonDirs($bundle);
        $skeletonDirs = array();

        if (is_dir($dir = $this->getContainer()->get('kernel')->getRootdir().'/Resources/MultiUserBundleMultiUserBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $reflClass = new \ReflectionClass($this);
        $dir = dirname($reflClass->getFileName());

        $skeletonDirs[] = $dir.'/../Resources/skeleton';
        $skeletonDirs[] = $dir.'/../Resources';

        return array_merge($skeletonDirs, $baseSkeletonDirs);
    }
}
