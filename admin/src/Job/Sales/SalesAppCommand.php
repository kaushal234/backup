<?php

declare(strict_types=1);

namespace App\Job\Sales;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'dms.inc.php';
include_once 'configurator.inc.php';

#[AsCommand(name: 'sales:cms')]
class SalesAppCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Synchronize configured files on owncloud for the CMS App';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $cfg, $OWNCLOUD_PROD_PATH, $SALES_APP_PATH;

        // DEV ONLY
        // $cfg["paths"]["salesapp"] = "/home/www/salesAppFolder";
        // $cfg["paths"]["cloudDEV"] = "/var/www/html/owncloud/data/salesapp_dev/files";

        $CLOUD_USER = $cfg['db']['owncloud']['user'];
        $CLOUD_ADDR = $cfg['db']['owncloud']['host'];
        $sac = new \tldSalesAppConfigurator();

        // Build local Sales App structure
        $location = $SALES_APP_PATH;

        $newDirectories = [];
        foreach ($sac->getRootElements() as $vars) {
            $element = new \tldSalesAppConfiguratorElement((int) $vars['id']);
            if ($element->isEnable()) {
                $path = $this->_getElementTreeView($element, $location);
                $newDirectories[] = $path;
            }
        }

        $newDirectories = array_merge(...$newDirectories);

        // Cleanup
        $dir_array = [];
        $dir = new \RecursiveDirectoryIterator($SALES_APP_PATH);
        $dir->setFlags(\FilesystemIterator::SKIP_DOTS);
        $filter = new MyRecursiveFilterIterator($dir);
        $objects = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            $dir_array[] = $name;
        }
        $cleanup = array_diff($dir_array, array_filter($newDirectories));
        foreach ($cleanup as $clean) {
            $this->rrmdir($clean);
            $this->logger->info("Deleting '$clean'");
        }

        // Synchronize to Cloud
        exec("chmod 775 $SALES_APP_PATH && chmod -R 755 $SALES_APP_PATH/*");
        exec("rsync -vazP -e 'ssh -o HostKeyAlgorithms=+ssh-dss -o PubkeyAcceptedKeyTypes=+ssh-rsa' --delete $SALES_APP_PATH/* $CLOUD_USER@$CLOUD_ADDR:$OWNCLOUD_PROD_PATH 2>&1", $output);
        $this->logger->info('Executed rsync', (array) $output);

        return Command::SUCCESS;
    }

    /**
     * @param \tldSalesAppConfiguratorElement $element
     * @param string                          $location
     *
     * @return array
     */
    private function _getElementTreeView($element, $location)
    {
        $parentLocation = $this->_createElement($element, $location);
        $paths[] = [$parentLocation];
        foreach ($element->getChildElements() as $vars) {
            $childElement = new \tldSalesAppConfiguratorElement((int) $vars['id']);
            if ($childElement->isEnable()) {
                $childPath = $this->_getElementTreeView($childElement, $parentLocation);
                $paths[] = $childPath;
            }
        }

        return array_merge(...$paths);
    }

    /**
     * @param \tldSalesAppConfiguratorElement $element
     * @param string                          $location
     *
     * @return string|void
     */
    private function _createElement($element, $location)
    {
        // Look options
        $elementType = $element->getOptionByKey('type');
        $elementLocation = $element->getOptionByKey('location');
        $elementEncryption = $element->getOptionByKey('encryption');
        // Depending on element type
        // -- Default info
        $value = $element->getValue();
        $id = (int) $element->getID();
        // -- Specific
        switch ($elementType['value']) {
            case 'FOLDER':
                $result = "$location/$value";
                if (file_exists($result)) {
                    return $result;
                }
                if (!mkdir($result) && !is_dir($result)) {
                    $this->logger->error("Error creating folder '$value'");
                } else {
                    $this->logger->info("'$result' created");

                    return $result;
                }

                break;
            case 'DMS':
                $dms = new \tldDMS($value);
                if ($dms->isEmpty()) {
                    $this->logger->warning("DMS#$value not found");

                    return;
                }
                $header = $dms->itsHeader;
                try {
                    $file = $dms->getActiveRevisionFile();
                } catch (\Exception $e) {
                    if ('No active revision' === $e->getMessage()) {
                        $this->logger->debug("DMS#$value: No Active Revision");

                        return;
                    }
                    $this->logger->error("DMS#$value: File not found");

                    return;
                }
                $filename = str_replace('/', '-', $header['title']).'_DMS#'.$value.'r'.$header['rev_id'].'.'.$file->getExtension();
                if ('Y' === $elementEncryption['value']) {
                    $filename .= '.enc';
                }
                $result = "$location/$filename";
                if (file_exists($result)) {
                    return $result;
                }
                if ('Y' === $elementEncryption['value']) {
                    // Copy Data + Encryption
                    $raw_data = file_get_contents($file->getFilepath());
                    $enc_data = $this->aes128_cbc_encrypt($raw_data);
                    $nfile = fopen($result, 'w');
                    fwrite($nfile, $enc_data);
                    fclose($nfile);
                    // Check if copy was successful
                    if (!file_exists($result)) {
                        $this->logger->error("Error creating encrypted DMS file '$value'");

                        return $result;
                    }
                    $this->logger->info("'$result' created");

                    return $result;
                }
                if ('N' === $elementEncryption['value']) {
                    if (!copy($file->getFilepath(), $result)) {
                        $this->logger->error("Error creating non-encrypted DMS file $filename");
                    } else {
                        $this->logger->info("'$result' created");

                        return $result;
                    }
                }

                break;
            case 'GALLERY':
                $file = $elementLocation['value'];
                // Numeric = ModFile
                if (is_numeric($file)) {
                    $modfile = new \tldModFile($file);
                    $headerFile = $modfile->getHeader();
                    if ((int) $headerFile['parent_id'] !== $id) {
                        $this->logger->error("Error: '$file' not linked to Element#$id");
                    } else {
                        $ext = mb_strtolower($headerFile['extension']);
                        if ('Y' === $elementEncryption['value']) {
                            $ext .= '.enc';
                        }
                    }
                // or URL
                } else {
                    $ext = mb_strtolower(pathinfo($file, \PATHINFO_EXTENSION));
                    if ('Y' === $elementEncryption['value']) {
                        $ext .= '.enc';
                    }
                }
                // Full path + file name
                $result = $location.'/'.str_replace('/', '-', $value).'_'.$id.'.'.$ext;
                if (file_exists($result)) {
                    return $result;
                }
                if ('Y' === $elementEncryption['value']) {
                    // Copy Data to SalesAppFolder + Encryption
                    if (is_numeric($file)) {
                        $raw_data = file_get_contents($headerFile['filepath']);
                        $enc_data = $this->aes128_cbc_encrypt($raw_data);
                        $nfile = fopen($result, 'w');
                        fwrite($nfile, $enc_data);
                        fclose($nfile);
                    } else {
                        $raw_data = file_get_contents($file);
                        $enc_data = $this->aes128_cbc_encrypt($raw_data);
                        $nfile = fopen($result, 'w');
                        fwrite($nfile, $enc_data);
                        fclose($nfile);
                    }
                    // Check if copy was successful
                    if (!file_exists($result)) {
                        $this->logger->error("Error creating encrypted GALLERY file '$value'");

                        return $result;
                    }
                    $this->logger->info("'$result' created");

                    return $result;
                }
                if ('N' === $elementEncryption['value']) {
                    // Copy Data to SalesAppFolder
                    if (is_numeric($file)) {
                        copy($headerFile['filepath'], $result);
                    } else {
                        copy($file, $result);
                    }
                    // Check if copy was successful
                    if (!file_exists($result)) {
                        $this->logger->error("Error creating non-encrypted GALLERY file '$value'");

                        return $result;
                    }
                    $this->logger->info("'$result' created");

                    return $result;
                }

                break;
            case 'LINK':
                $youtubeId = $elementLocation['value'];
                $result = $location.'/'.str_replace(['/', '@'], '-', $value).'.youtube';
                if (file_exists($result)) {
                    return $result;
                }

                if (!file_put_contents($result, $youtubeId)) {
                    $this->logger->error("Error creating LINK file '$value'");
                } else {
                    $this->logger->info("'$result' created");

                    return $result;
                }

                break;
        }
    }

    private function aes128_cbc_encrypt($data)
    {
        $keyBase64 = 'Mi5NsRnRPT4Zd5hz5YzP7ApPRJe22N7Yi0LLaL/qmbVkJIVnGwf24rg49epv4/07MngwRg3FJafF/7pQ0laNXGds1/ctWOj+xw4PbBrkDwqazWg0n7XQIlkccYvX0xfzdh25EhsqgLGJo6OKhUHaNecg1wrxsIxa2ij02Xxsz3U=';

        // never replace subst by mb_substr
        $key = substr(base64_decode($keyBase64), 0, 32);
        $iv = str_repeat("\0", 16);
        // never replace strlen by mb_strlen
        $padding = 16 - (\strlen($data) % 16);

        $data .= str_repeat(\chr($padding), $padding);

        return openssl_encrypt($data, 'AES-256-CBC', $key, \OPENSSL_RAW_DATA | \OPENSSL_ZERO_PADDING, $iv);
    }

    private function rrmdir($dir)
    {
        if (!is_dir($dir) || is_link($dir)) {
            return unlink($dir);
        }
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ('.' !== $object && '..' !== $object) {
                    if ('dir' === filetype($dir.'/'.$object)) {
                        $this->rrmdir($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}

class MyRecursiveFilterIterator extends \RecursiveFilterIterator
{
    public static $FILTERS = [
        '_config', 'config.xlsx', 'youtube_icon.jpg', 'pdf_icon.jpg',
    ];

    public function accept(): bool
    {
        return !\in_array(
            $this->current()->getFilename(),
            self::$FILTERS,
            true
        );
    }
}
