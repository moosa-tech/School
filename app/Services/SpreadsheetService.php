<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SpreadsheetService
{
    /**
     * @return list<list<bool|float|int|string|null>>
     */
    public function readUploadedFile(UploadedFile $file, int $maximumDataRows): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $reader = match ($extension) {
            'csv' => new CsvReader,
            'xlsx' => new XlsxReader,
            default => throw ValidationException::withMessages([
                'file' => 'Upload a CSV or XLSX file.',
            ]),
        };

        $path = $file->getRealPath();
        if (! is_string($path)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded spreadsheet could not be read.',
            ]);
        }

        $rows = [];

        try {
            $reader->open($path);

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = array_map(
                        fn (mixed $value): bool|float|int|string|null => $this->normalizeCell($value),
                        array_values($row->toArray())
                    );

                    if (count($rows) > $maximumDataRows + 1) {
                        throw ValidationException::withMessages([
                            'file' => "The spreadsheet may contain no more than {$maximumDataRows} data rows.",
                        ]);
                    }
                }

                break;
            }
        } catch (IOException) {
            throw ValidationException::withMessages([
                'file' => 'The spreadsheet file is invalid or could not be read.',
            ]);
        } finally {
            $reader->close();
        }

        return $rows;
    }

    /**
     * @param  iterable<list<mixed>>  $rows
     */
    public function download(iterable $rows, string $filename): BinaryFileResponse
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $writer = match ($extension) {
            'csv' => new CsvWriter,
            'xlsx' => new XlsxWriter,
            default => throw new \InvalidArgumentException('Only CSV and XLSX downloads are supported.'),
        };

        $disk = Storage::disk('local');
        $disk->makeDirectory('exports');
        $path = $disk->path('exports/'.Str::uuid().'.'.$extension);

        $writer->openToFile($path);

        try {
            foreach ($rows as $row) {
                $writer->addRow(Row::fromValues(array_map(
                    fn (mixed $value): mixed => $this->safeSpreadsheetValue($value),
                    array_values($row)
                )));
            }
        } finally {
            $writer->close();
        }

        $response = response()->download($path, basename($filename));
        $response->deleteFileAfterSend(true);

        return $response;
    }

    private function normalizeCell(mixed $value): bool|float|int|string|null
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_scalar($value) || $value === null) {
            return $value;
        }

        return null;
    }

    private function safeSpreadsheetValue(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
