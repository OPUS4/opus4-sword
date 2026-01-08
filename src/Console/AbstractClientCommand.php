<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Sword\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function file_put_contents;
use function rtrim;

/**
 * Console command for retrieving service document from SWORD service.
 *
 * TODO other authentication methods?
 * TODO optionally get user and password from configuration (simplifies using client with a specific server)
 * TODO define shortcuts for options?
 */
abstract class AbstractClientCommand extends Command
{
    public const ARGUMENT_SWORD_SERVER = 'Server';

    public const OPTION_USERNAME = 'user';

    public const OPTION_OUTPUT = 'output';

    protected function configure()
    {
        parent::configure();

        $this->addArgument(
            self::ARGUMENT_SWORD_SERVER,
            InputArgument::REQUIRED,
            'URL of SWORD service'
        )
            ->addOption(
                self::OPTION_USERNAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Username for HTTP basic authentication'
            )
            ->addOption(
                self::OPTION_OUTPUT,
                null,
                InputOption::VALUE_REQUIRED,
                'Output file'
            );
    }

    protected function getSwordUrl(InputInterface $input, OutputInterface $output): string
    {
        $serverUrl = $input->getArgument(self::ARGUMENT_SWORD_SERVER);
        return rtrim($serverUrl, ' /');
    }

    protected function getClientOptions(InputInterface $input, OutputInterface $output): array
    {
        $options = [];

        $username = $input->getOption(self::OPTION_USERNAME);

        if ($username !== null) {
            $helper   = new QuestionHelper();
            $question = new Question("Password for user '{$username}': ");
            $question->setHidden(true);
            $question->setHiddenFallback(false);

            $password = $helper->ask($input, $output, $question);

            $options = [
                'auth_basic' => [$username, $password],
            ];
        }

        return $options;
    }

    protected function writeResponse(string $response, InputInterface $input, OutputInterface $output): void
    {
        $outputFile = $input->getOption(self::OPTION_OUTPUT);

        if ($outputFile !== null) {
            file_put_contents($outputFile, $response);
        } else {
            $output->writeln($response);
        }
    }
}
