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

use Opus\Import\PackageHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\MimeTypes;

use function file_exists;

/**
 * Console command for validating a SWORD package.
 *
 * This command verifies the structure and XML of an OPUS 4 SWORD package.
 */
class ValidateCommand extends Command
{
    public const ARGUMENT_SWORD_FILE = 'File';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Validating OPUS 4 Sword package.

THIS COMMAND IS NOT IMPLEMENTED YET.
EOT;

        $this->setName('sword:validate')
            ->setDescription('Validate OPUS 4 Sword package')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_SWORD_FILE,
                InputArgument::REQUIRED,
                'Sword package file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument(self::ARGUMENT_SWORD_FILE);

        if (! file_exists($filePath)) {
            $output->writeln("'{$filePath}' not found");
            return self::FAILURE;
        }

        $mimeTypes = new MimeTypes();
        $mimeType  = $mimeTypes->guessMimeType($filePath);

        $packageHandler = PackageHandler::getPackageHandler($mimeType);

        if ($packageHandler === null) {
            $output->writeln("No package handler found for type '{$mimeType}'");
            return self::FAILURE;
        }

        // TODO use validate function of PackageHandlerInterface

        return self::SUCCESS;
    }
}
