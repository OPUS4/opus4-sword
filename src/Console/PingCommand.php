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

use DOMDocument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Console command for retrieving service document from SWORD service.
 *
 * TODO other authentication methods?
 * TODO optionally get user and password from configuration (simplifies using client with a specific server)
 */
class PingCommand extends AbstractClientCommand
{
    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Retrieve service document from SWORD service.
EOT;

        $this->setName('sword:ping')
            ->setDescription('Retrieve service document')
            ->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $serverUrl = $this->getSwordUrl($input, $output);
        $swordUrl  = "{$serverUrl}/servicedocument";
        $options   = $this->getClientOptions($input, $output);

        $output->writeln("Requesting service document from '{$swordUrl}'", OutputInterface::VERBOSITY_VERBOSE);

        $client   = HttpClient::create();
        $response = $client->request(
            'GET',
            $swordUrl,
            $options,
        );

        $xml = $response->getContent();

        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput       = true;
        $dom->loadXML($xml);

        $this->writeResponse($dom->saveXML(), $input, $output);

        return self::SUCCESS;
    }
}
