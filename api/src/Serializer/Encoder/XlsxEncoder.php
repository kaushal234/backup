<?php

declare(strict_types=1);

namespace App\Serializer\Encoder;

use App\Event\SpreadsheetGeneratedEvent;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class XlsxEncoder implements EncoderInterface
{
    /**
     * @var string
     */
    final public const FORMAT = 'xlsx';

    private readonly CsvEncoder $csvEncoder;
    private readonly EventDispatcherInterface $eventDispatcher;
    private readonly ParameterBagInterface $parameters;

    public function __construct(CsvEncoder $csvEncoder, ParameterBagInterface $parameters, EventDispatcherInterface $eventDispatcher)
    {
        $this->csvEncoder = $csvEncoder;
        $this->parameters = $parameters;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function encode(mixed $data, string $format, array $context = []): string
    {
        $path = (string) tempnam($this->parameters->get('kernel.cache_dir'), 'csv');
        file_put_contents($path, $this->csvEncoder->encode($data, CsvEncoder::FORMAT, $context));

        if ('cli' !== \PHP_SAPI) {
            Settings::setCache(new Psr16Cache(new ApcuAdapter(md5($path), 300)));
        }

        $reader = new Csv();
        $reader->setReadDataOnly(true);

        try {
            $spreadsheet = $reader->load($path);
        } catch (Exception $e) {
            $spreadsheet = new Spreadsheet();
        }

        $this->eventDispatcher->dispatch(new SpreadsheetGeneratedEvent($spreadsheet, $context));

        $writer = new Xlsx($spreadsheet);
        unlink($path);

        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format): bool
    {
        return self::FORMAT === $format;
    }

    public static function xlsSanitize(?string $value): ?string
    {
        if (null === $value) {
            return '';
        }

        return str_replace(["\n", "\r", '"'], ['', '', '\'\''], mb_ltrim($value, '='));
    }
}
