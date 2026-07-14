<?php

declare(strict_types=1);

namespace App\Services;

class SpreadsheetExporter
{
    public function csv(array $headers, array $rows): string
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($stream, array_map([$this, 'safeCsvValue'], $row), ';');
        }
        rewind($stream);
        return (string) stream_get_contents($stream);
    }

    public function xlsx(string $title, array $headers, array $rows): string
    {
        $sheetRows = array_merge([$headers], $rows);
        $xmlRows = [];
        foreach ($sheetRows as $rowIndex => $row) {
            $cells = [];
            foreach (array_values($row) as $columnIndex => $value) {
                $ref = $this->column($columnIndex + 1).($rowIndex + 1);
                if (is_int($value) || is_float($value) || (is_numeric($value) && ! preg_match('/^0\d+$/', (string) $value))) {
                    $cells[] = '<c r="'.$ref.'"'.($rowIndex === 0 ? ' s="1"' : '').'><v>'.(float) $value.'</v></c>';
                } else {
                    $cells[] = '<c r="'.$ref.'" t="inlineStr"'.($rowIndex === 0 ? ' s="1"' : '').'><is><t xml:space="preserve">'.$this->xml((string) $value).'</t></is></c>';
                }
            }
            $xmlRows[] = '<row r="'.($rowIndex + 1).'">'.implode('', $cells).'</row>';
        }
        $lastColumn = $this->column(max(1, count($headers)));
        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.$this->xml(mb_substr($title, 0, 31)).'" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF102A43"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="2"><xf fontId="0" fillId="0" borderId="0" xfId="0"/><xf fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs></styleSheet>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:'.$lastColumn.max(1, count($sheetRows)).'"/><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><sheetFormatPr defaultRowHeight="15"/><sheetData>'.implode('', $xmlRows).'</sheetData><autoFilter ref="A1:'.$lastColumn.max(1, count($sheetRows)).'"/></worksheet>',
        ];
        return $this->zip($files);
    }

    private function zip(array $files): string
    {
        $body = '';
        $central = '';
        $offset = 0;
        foreach ($files as $name => $content) {
            $name = str_replace('\\', '/', $name);
            $compressed = gzdeflate($content, 6);
            $crc = crc32($content);
            $local = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 8, 0, 0, $crc, strlen($compressed), strlen($content), strlen($name), 0).$name.$compressed;
            $body .= $local;
            $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 8, 0, 0, $crc, strlen($compressed), strlen($content), strlen($name), 0, 0, 0, 0, 0, $offset).$name;
            $offset += strlen($local);
        }
        return $body.$central.pack('VvvvvVVv', 0x06054b50, 0, 0, count($files), count($files), strlen($central), strlen($body), 0);
    }

    private function column(int $number): string
    {
        $result = '';
        while ($number > 0) {
            $number--;
            $result = chr(65 + ($number % 26)).$result;
            $number = intdiv($number, 26);
        }
        return $result;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function safeCsvValue(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+@-]/', ltrim($value))) {
            return "'".$value;
        }
        return $value;
    }
}
