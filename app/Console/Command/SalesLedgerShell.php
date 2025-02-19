<?php
App::uses('AppShell', 'Console/Command');
App::uses('HttpSocket', 'Network/Http');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
require 'vendor/autoload.php'; // Make sure Composer's autoload file is included

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesLedgerShell extends Shell {
    private $uploadsPath;
    private $processedPath;
    private $failedPath;
    private $backupsPath;
    private $masterLedgerPath;
    private $processedFilesLog;
    private $headers = [
        'File Name',
        'Seller Name',
        'Purchaser Name',
        'Sold Party Details',
        'Category ID',
        'Invoice Date',
        'Due Date',
        'Document Number',
        'Item Name',
        'Item Price',
        'Item Quantity',
        'Invoice Title'
    ];

    public function initialize() {
        parent::initialize();
        
        // Initialize paths
        $this->uploadsPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'uploads';
        $this->processedPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'processed';
        $this->failedPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'failed';
        $this->backupsPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'backups';
        $this->masterLedgerPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'master_ledger.xlsx';
        $this->processedFilesLog = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'processed_files.json';
    }

    public function main() {
        $this->setupDirectories();
        $this->createMasterLedgerIfNotExists();
        $this->backupMasterLedger();

        $files = $this->getUnprocessedFiles();

        if (empty($files)) {
            $this->out('No new files to process.');
            return;
        }

        $processedFiles = $this->getProcessedFilesList();
        $existingData = $this->readMasterLedger();
        $newData = [];

        foreach ($files as $file) {
            $this->out("Processing: {$file}");

            try {
                $responseData = json_decode($this->processFile($file), true);
                if ($responseData) {
                    $newData = array_merge($newData, $this->formatResponseData($file, $responseData));
                    $this->moveToProcessed($file);
                    $processedFiles[] = $file;
                }
            } catch (Exception $e) {
                $this->handleProcessingError($file, $e);
            }
        }

        if (!empty($newData)) {
            $this->updateMasterLedger($existingData, $newData);
        }

        $this->saveProcessedFilesList($processedFiles);
        $this->out('Processing completed successfully.');
    }

    private function setupDirectories() {
        $directories = [$this->uploadsPath, $this->processedPath, $this->failedPath, $this->backupsPath];
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    // private function createMasterLedgerIfNotExists() {
    //     if (!file_exists($this->masterLedgerPath)) {
    //         $this->createExcelFile($this->masterLedgerPath, [$this->headers]);
    //     }
    // }
    private function createMasterLedgerIfNotExists() {
        if (!file_exists($this->masterLedgerPath)) {
            // Use updateMasterLedger with empty existing data to create new file
            $this->updateMasterLedger([], []);
        }
    }

    private function createExcelFile($filePath, $data) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        $col = 'A';
        foreach ($this->headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Add data
        $rowNum = 2;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $rowNum, $cell);
                $col++;
            }
            $rowNum++;
        }

        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        $this->out("Excel file created at {$filePath}.");
    }

    private function backupMasterLedger() {
        if (file_exists($this->masterLedgerPath)) {
            $backupFile = $this->backupsPath . DS . 'backup_' . date('Y-m-d_H-i-s') . '.xlsx';
            copy($this->masterLedgerPath, $backupFile);

            // Keep only last 10 backups
            $backups = glob($this->backupsPath . DS . '*');
            if (count($backups) > 10) {
                array_map('unlink', array_slice($backups, 0, count($backups) - 10));
            }
        }
    }

    private function getUnprocessedFiles() {
        $processedFiles = $this->getProcessedFilesList();
        return array_filter(scandir($this->uploadsPath), function($file) use ($processedFiles) {
            return $file !== '.' && $file !== '..' && is_file($this->uploadsPath . DS . $file) && !in_array($file, $processedFiles);
        });
    }

    private function getProcessedFilesList() {
        return file_exists($this->processedFilesLog) ? json_decode(file_get_contents($this->processedFilesLog), true) : [];
    }

    private function saveProcessedFilesList($files) {
        file_put_contents($this->processedFilesLog, json_encode($files));
    }

    private function processFile(string $file): ?string
    {
        $filePath = $this->uploadsPath . DS . $file;
    
        try {
            // Initialize cURL session
            $ch = curl_init();
    
            // Get the mime type of the file
            $fileMimeType = mime_content_type($filePath); // This will give us the file MIME type dynamically
    
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/upload-invoice'); // The API endpoint
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Set timeout (in seconds)
    
            // Prepare file for upload
            $postFields = [
                'file' => new CURLFile($filePath, $fileMimeType, $file) // Attach file using CURLFile and set the correct MIME type
            ];
    
            // Set POST fields (file upload)
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    
            // Execute cURL request
            $response = curl_exec($ch);
    
            // Check for errors
            if ($response === false) {
                throw new Exception("cURL error: " . curl_error($ch));
            }
    
            // Get the HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
            // Close the cURL session
            curl_close($ch);
    
            // Check if the request was successful
            if ($httpCode == 200) {
                return json_decode($response, true); // Parse and return the JSON response
            }
    
            // If not successful, throw an exception
            throw new Exception("API request failed with HTTP code $httpCode: $response");
    
        } catch (Exception $e) {
            // Log the error
            CakeLog::write('error', "File processing failed for {$file}: " . $e->getMessage());
            throw $e;  // Rethrow the exception
        }
    }        

    // private function formatResponseData($fileName, $responseData) {
    //     $formattedData = [];
    //     foreach ($responseData['invoices'] as $invoice) {
    //         foreach ($invoice['items'] as $item) {
    //             $formattedData[] = [
    //                 $fileName,
    //                 $responseData['seller_name'] ?? '',
    //                 $responseData['purchaser_name'] ?? '',
    //                 $responseData['sold_party_details'] ?? '',
    //                 $responseData['category_id'][0] ?? '',
    //                 $responseData['datepicker']['invoice_date'] ?? '',
    //                 $responseData['datepicker']['due_date'] ?? '',
    //                 $invoice['document_number'] ?? '',
    //                 $item['name'] ?? '',
    //                 $item['price'] ?? '',
    //                 $item['quantity'] ?? '',
    //                 $invoice['title'] ?? ''
    //             ];
    //         }
    //     }
    //     return $formattedData;
    // }
    private function formatResponseData($fileName, $responseData) {
        $formattedData = [];
        if (!empty($responseData['invoices'])) {
            foreach ($responseData['invoices'] as $invoice) {
                foreach ($invoice['items'] as $item) {
                    $row = [
                        $this->cleanValue($fileName),
                        $this->cleanValue($responseData['seller_name'] ?? ''),
                        $this->cleanValue($responseData['purchaser_name'] ?? ''),
                        $this->cleanValue($responseData['sold_party_details'] ?? ''),
                        $this->cleanValue($responseData['category_id'][0] ?? ''),
                        $this->cleanValue($responseData['datepicker']['invoice_date'] ?? ''),
                        $this->cleanValue($responseData['datepicker']['due_date'] ?? ''),
                        $this->cleanValue($invoice['document_number'] ?? ''),
                        $this->cleanValue($item['name'] ?? ''),
                        $this->cleanNumericValue($item['price'] ?? ''),
                        $this->cleanNumericValue($item['quantity'] ?? ''),
                        $this->cleanValue($invoice['title'] ?? '')
                    ];

                    if (array_filter($row)) {
                        $formattedData[] = $row;
                    }
                }
            }
        }
        return $formattedData;
    }

    private function cleanValue($value) {
        if (empty($value) || $value === 'string' || $value === 'null' || $value === null) {
            return '';
        }
        // Remove any leading/trailing whitespace and convert to string
        return trim((string)$value);
    }

    private function cleanNumericValue($value) {
        if (empty($value) || $value === 'string' || $value === 'null' || $value === null) {
            return '';
        }
        // Remove commas and ensure numeric value
        $numericValue = str_replace(',', '', $value);
        return is_numeric($numericValue) ? $numericValue : '';
    }

    // private function readMasterLedger() {
    //     if (!file_exists($this->masterLedgerPath)) {
    //         return [];
    //     }

    //     $spreadsheet = IOFactory::load($this->masterLedgerPath);
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $data = $sheet->toArray(null, true, true, true);

    //     // Remove header row
    //     return array_slice($data, 1);
    // }
    private function readMasterLedger() {
        if (!file_exists($this->masterLedgerPath)) {
            return [];
        }

        $spreadsheet = IOFactory::load($this->masterLedgerPath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);

        // Remove header row and return only data
        array_shift($data);
        return array_values($data);
    }

    // private function updateMasterLedger($existingData, $newData) {
    //     $allData = array_merge([$this->headers], $existingData, $newData);
    //     $this->createExcelFile($this->masterLedgerPath, $allData);
    //     $this->out("Master ledger updated successfully.");
    // }
    private function updateMasterLedger($existingData, $newData) {
        // Create new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers only once at the top
        $col = 'A';
        foreach ($this->headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        // Add existing and new data starting from row 2
        $rowNum = 2;
        
        // Add existing data
        foreach ($existingData as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $rowNum, $this->cleanValue($cell));
                $col++;
            }
            $rowNum++;
        }

        // Add new data
        foreach ($newData as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $rowNum, $this->cleanValue($cell));
                $col++;
            }
            $rowNum++;
        }

        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($this->masterLedgerPath);
        $this->out("Master ledger updated successfully. Total rows: " . ($rowNum - 1));
    }


    private function moveToProcessed($file) {
        rename($this->uploadsPath . DS . $file, $this->processedPath . DS . $file);
    }

    private function handleProcessingError($file, $e) {
        $this->log("Error processing {$file}: " . $e->getMessage(), 'error');
        rename($this->uploadsPath . DS . $file, $this->failedPath . DS . $file);
    }
}
