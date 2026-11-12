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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Sword\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\MimeTypes;

use function file_exists;
use function fopen;

/**
 * Console command for depositing a SWORD package via HTTP(S).
 */
class DepositCommand extends AbstractClientCommand
{
    public const ARGUMENT_SWORD_FILE = 'File';

    protected function configure(): void
    {
        parent::configure();

        $help = <<<EOT
Depositing OPUS 4 SWORD package.
EOT;

        $this->setName('sword:deposit')
            ->setDescription('Deposit OPUS 4 SWORD package')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_SWORD_FILE,
                InputArgument::REQUIRED,
                'SWORD package file'
            );
    }

    /**
     * TODO How to output error document?
     * TODO move OPUS 4 SWORD client code into separate class
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument(self::ARGUMENT_SWORD_FILE);

        if (! file_exists($filePath)) {
            $output->writeln("'{$filePath}' not found");
            return self::FAILURE;
        }
        $output->writeln($filePath, OutputInterface::VERBOSITY_DEBUG);

        $mimeTypes = new MimeTypes();
        $mimeType  = $mimeTypes->guessMimeType($filePath);
        $output->writeln("MIME-Type: {$mimeType}", OutputInterface::VERBOSITY_DEBUG);

        $swordUrl      = $this->getSwordUrl($input, $output);
        $clientOptions = $this->getClientOptions($input, $output);

        $clientOptions['body']    = fopen($filePath, 'r');
        $clientOptions['headers'] = [
            'Content-Type' => $mimeType,
        ];

        $client   = HttpClient::create();
        $response = $client->request(
            'POST',
            $swordUrl . '/deposit',
            $clientOptions,
        );

        // TODO parse/format XML response
        $this->writeResponse($response->getContent(), $input, $output);

        return self::SUCCESS;
    }
}
