<?php
App::uses('AppShell', 'Console/Command');
App::uses('HttpSocket', 'Network/Http');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BankStatementShell extends Shell {
    private $uploadsPath;
    private $processedPath;
    private $failedPath;
    private $backupsPath;
    private $masterLedgerPath;
    private $processedFilesLog;
    private $headers = [
        'File Name',
        'Bank Name',
        'Account Holder Name',
        'Account Number',
        'Statement Date',
        'Currency',
        'Transaction Date',
        'Description',
        'Debit',
        'Credit',
        'Balance',
        'Category',
        'Type'
    ];

    public function initialize() {
        parent::initialize();
        
        $this->uploadsPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'uploads';
        $this->processedPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'processed';
        $this->failedPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'failed';
        $this->backupsPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'backups';
        $this->masterLedgerPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'master_ledger.xlsx';
        $this->processedFilesLog = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'processed_files.json';
    }

    public function main() {
        $this->setupDirectories();
        $this->createMasterLedgerIfNotExists();
        $this->backupMasterLedger();
        
        $files = $this->getUnprocessedFiles();
        
        if (empty($files)) {
            $this->out('No new bank statements to process.');
            return;
        }

        $processedFiles = $this->getProcessedFilesList();
        $existingData = $this->readMasterLedger();
        $newData = [];

        foreach ($files as $file) {
            $this->out("Processing: {$file}");
            
            try {
                $response = $this->processFile($file);
                $responseData = json_decode($response, true);
                
                if ($responseData) {
                    $this->out('Processing successful.');
                    $formattedData = $this->formatResponseData($file, $responseData);
                    $newData = array_merge($newData, $formattedData);
                    
                    // // Insert into SQL table
                    // $this->insertIntoEntriesTable($formattedData);
                    $this->moveToProcessed($file);
                    $processedFiles[] = $file;
                } else {
                    throw new Exception("Invalid JSON response or empty data");
                }
            } catch (Exception $e) {
                $this->handleProcessingError($file, $e);
            }
        }
        
        if (!empty($newData)) {
            $this->updateMasterLedger($existingData, $newData);
        }
        
        $this->saveProcessedFilesList($processedFiles);
        $this->out('Bank statement processing completed successfully.');
    }
    
    private function insertIntoEntriesTable($entries) {
        $this->out('Inserting entries into the database...');
        $Entry = ClassRegistry::init('Entry');
        foreach ($entries as $entry) {
            $data = [
                'Entry' => [
                    'tag_id' => $entry['tag_id'] ?? null,
                    'entrytype_id' => '1',
                    'number' => $entry['number'] ?? null,
                    'date' => date('Y-m-d', strtotime($entry['transaction_date'])),
                    'dr_total' => $entry['debit'] ?? 0.00,
                    'cr_total' => $entry['credit'] ?? 0.00,
                    'narration' => $entry['category']
                ]
            ];
            $Entry->create();
            if (!$Entry->save($data)) {
                $this->out("Failed to insert entry: " . json_encode($data));
            }
        }
    }
    
    private function setupDirectories() {
        $directories = [$this->uploadsPath, $this->processedPath, $this->failedPath, $this->backupsPath];
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    private function createMasterLedgerIfNotExists() {
        if (!file_exists($this->masterLedgerPath)) {
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
    
            // Keep only the last 10 backups
            $backups = glob($this->backupsPath . DS . '*.xlsx');
            if (count($backups) > 10) {
                array_map('unlink', array_slice($backups, 0, count($backups) - 10));
            }
    
            $this->out("Master ledger backup created: " . $backupFile);
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
    
    private function processFile(string $file): ?string {
        $filePath = $this->uploadsPath . DS . $file;
        
        try {
            $ch = curl_init();
            $fileMimeType = mime_content_type($filePath);
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/upload-bank-statement');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            $postFields = ['file' => new CURLFile($filePath, $fileMimeType, $file)];
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            
            $response = curl_exec($ch);
            
            if ($response === false) {
                throw new Exception("cURL error: " . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                return $response;
            }
            
            throw new Exception("API request failed with HTTP code $httpCode: $response");
        } catch (Exception $e) {
            CakeLog::write('error', "File processing failed for {$file}: " . $e->getMessage());
            throw $e;
        }
    }

    private function formatResponseData($fileName, $responseData) {
        $formattedData = [];
        
        // Check if we have bankStatements data in the response
        if (!empty($responseData['bankStatements'])) {
            foreach ($responseData['bankStatements'] as $bankStatement) {
                $bankName = $this->cleanValue($bankStatement['bankName'] ?? '');
                $accountHolderName = $this->cleanValue($bankStatement['accountHolderName'] ?? '');
                $accountNumber = $this->cleanValue($bankStatement['accountNumber'] ?? '');
                $statementDate = $this->cleanValue($bankStatement['statementDate'] ?? '');
                $currency = $this->cleanValue($bankStatement['currency'] ?? '');
                
                if (!empty($bankStatement['transactions']) && is_array($bankStatement['transactions'])) {
                    foreach ($bankStatement['transactions'] as $transaction) {
                        $formattedData[] = [
                            $fileName,
                            $bankName,
                            $accountHolderName,
                            $accountNumber,
                            $statementDate,
                            $currency,
                            $this->cleanValue($transaction['date'] ?? ''),
                            $this->cleanValue($transaction['description'] ?? ''),
                            $this->cleanNumericValue($transaction['debit'] ?? ''),
                            $this->cleanNumericValue($transaction['credit'] ?? ''),
                            $this->cleanNumericValue($transaction['balance'] ?? ''),
                            $this->cleanValue($transaction['category'] ?? 'Others'),
                            $this->cleanValue($transaction['type'] ?? 'Others')
                        ];
                    }
                }
            }
        } 
        // Check alternative structure (bank_statements)
        else if (!empty($responseData['bank_statements'])) {
            foreach ($responseData['bank_statements'] as $bankStatement) {
                $bankName = $this->cleanValue($bankStatement['bank_name'] ?? '');
                
                // Handle account holder name if it's an array or string
                $accountHolderName = '';
                if (!empty($bankStatement['account_holder_name'])) {
                    $accountHolderName = is_array($bankStatement['account_holder_name']) 
                        ? implode(' ', array_filter($bankStatement['account_holder_name'])) 
                        : $this->cleanValue($bankStatement['account_holder_name']);
                }
                
                $accountNumber = $this->cleanValue($bankStatement['account_number'] ?? '');
                $statementDate = $this->cleanValue($bankStatement['statement_date'] ?? '');
                $currency = $this->cleanValue($bankStatement['currency'] ?? '');
                
                if (!empty($bankStatement['transactions']) && is_array($bankStatement['transactions'])) {
                    foreach ($bankStatement['transactions'] as $transaction) {
                        $formattedData[] = [
                            $fileName,
                            $bankName,
                            $accountHolderName,
                            $accountNumber,
                            $statementDate,
                            $currency,
                            $this->cleanValue($transaction['date'] ?? ''),
                            $this->cleanValue($transaction['description'] ?? ''),
                            $this->cleanNumericValue($transaction['debit'] ?? ''),
                            $this->cleanNumericValue($transaction['credit'] ?? ''),
                            $this->cleanNumericValue($transaction['balance'] ?? ''),
                            $this->cleanValue($transaction['category'] ?? 'Others'),
                            $this->cleanValue($transaction['type'] ?? 'Others')
                        ];
                    }
                }
            }
        }
        
        return $formattedData;
    }
    
    private function cleanValue($value) {
        if (empty($value) || in_array($value, ['string', 'null', null], true)) {
            return '';
        }
        return trim((string)$value);
    }
    
    private function cleanNumericValue($value) {
        if (empty($value) || in_array($value, ['string', 'null', null], true)) {
            return '';
        }
        // Remove commas and ensure it's a valid number
        $numericValue = str_replace(',', '', $value);
        return is_numeric($numericValue) ? number_format((float)$numericValue, 3, '.', '') : '';
    }
    
    private function readMasterLedger() {
        if (!file_exists($this->masterLedgerPath)) {
            return [];
        }
    
        $spreadsheet = IOFactory::load($this->masterLedgerPath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);
    
        // Remove the header row and return only data
        array_shift($data);
        return array_values($data);
    }
    
    private function updateMasterLedger($existingData, $newData) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Add headers to the first row
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
            foreach ($row as $index => $cell) {
                $sheet->setCellValue($col . $rowNum, $cell);
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
        $this->err("Error processing {$file}: " . $e->getMessage());
        $this->log("Error processing {$file}: " . $e->getMessage(), 'error');
        rename($this->uploadsPath . DS . $file, $this->failedPath . DS . $file);
    }
}