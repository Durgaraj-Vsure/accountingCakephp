<?php
App::uses('WebzashAppController', 'Webzash.Controller');

// Include Composer autoload (since vendor is in App/Vendor)
require_once(APP . 'Vendor' . DS . 'autoload.php'); // Adjust the path as needed

use PhpOffice\PhpSpreadsheet\IOFactory;

class FileUploadController extends WebzashAppController {

    public $uses = array(); 
    var $layout = 'default';  
    public $uploadsPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'uploads' . DS;
    public $processedPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'processed' . DS;
    public $failedPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'failed' . DS;
    public $masterLedgerPath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'master_ledger.xlsx';
    public $bankUploadsPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'uploads' . DS;
    public $bankProcessedPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'processed' . DS;
    public $bankFailedPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'failed' . DS;
    public $bankLedgerPath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'master_ledger.xlsx';

    public function index() {
        $this->set('title_for_layout', __d('webzash', 'File Upload'));
    
        if ($this->request->is('post')) {
            if (empty($this->request->data['FileUpload']['category'])) {
                $this->Session->setFlash(__d('webzash', 'Invalid category selected!'), 'default', array('class' => 'alert alert-danger'));
                return $this->redirect(array('action' => 'index'));
            }
    
            $category = $this->request->data['FileUpload']['category'];
    
            // Define the upload path based on category
            $uploadDirectories = [
                'sales' => APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'uploads' . DS,
                'bank_statement' => APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'uploads' . DS
            ];
    
            if (!isset($uploadDirectories[$category])) {
                $this->Session->setFlash(__d('webzash', 'Invalid category selected!'), 'default', array('class' => 'alert alert-danger'));
                return $this->redirect(array('action' => 'index'));
            }
    
            $uploadsPath = $uploadDirectories[$category];
    
            // Check for file upload
            if (!empty($this->request->data['FileUpload']['file']['name'])) {
                $file = $this->request->data['FileUpload']['file'];
    
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $targetFile = $uploadsPath . basename($file['name']);
    
                    if (!is_dir($uploadsPath)) {
                        mkdir($uploadsPath, 0777, true);
                    }
    
                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                        $this->Session->setFlash(__d('webzash', 'File has been uploaded successfully!'), 'default', array('class' => 'alert alert-success'));
                    } else {
                        $this->Session->setFlash(__d('webzash', 'Failed to upload file! Please try again.'), 'default', array('class' => 'alert alert-danger'));
                    }
                } else {
                    $this->Session->setFlash(__d('webzash', 'Error uploading file. Please try again.'), 'default', array('class' => 'alert alert-danger'));
                }
            } else {
                $this->Session->setFlash(__d('webzash', 'No file selected!'), 'default', array('class' => 'alert alert-danger'));
            }
        }
    
        // Count files in each directory
        $salesUploadsCount = $this->countFilesInDirectory($this->uploadsPath);
        $salesProcessedCount = $this->countFilesInDirectory($this->processedPath);
        $salesFailedCount = $this->countFilesInDirectory($this->failedPath);
        $bankUploadsCount = $this->countFilesInDirectory($this->bankUploadsPath);
        $bankProcessedCount = $this->countFilesInDirectory($this->bankProcessedPath);
        $bankFailedCount = $this->countFilesInDirectory($this->bankFailedPath);
    
        $uploadsCount = $salesUploadsCount + $bankUploadsCount;
        $processedCount = $salesProcessedCount + $bankProcessedCount;
        $failedCount = $salesFailedCount + $bankFailedCount;
    
        $this->set(compact('uploadsCount', 'processedCount', 'failedCount'));
    
        // Load Sales Ledger Excel Data
        $salesData = [];
        if (file_exists($this->masterLedgerPath)) {
            $salesData = $this->loadExcelData($this->masterLedgerPath);
        }
    
        // Load Bank Statement Excel Data
        $bankData = [];
        if (file_exists($this->bankLedgerPath)) {
            $bankData = $this->loadExcelData($this->bankLedgerPath);
        }
    
        $this->set(compact('salesData', 'bankData'));
    }
    
    /**
     * Helper function to read an Excel file and return data as an array
     */
    private function loadExcelData($filePath) {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = [];
    
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
    
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getFormattedValue();
            }
    
            $rows[] = $rowData;
        }
    
        return $rows;
    }    
    

    public function download() {
        // Check if the category is selected
        if (empty($this->request->data['FileUpload']['category'])) {
            $this->Session->setFlash(__d('webzash', 'Invalid category selected!'), 'default', array('class' => 'alert alert-danger'));
            return $this->redirect(array('action' => 'index'));
        }
    
        $category = $this->request->data['FileUpload']['category'];
    
        // Define the download paths based on the selected category
        if ($category == 'sales') {
            $filePath = APP . 'webroot' . DS . 'uploads' . DS . 'sales_files' . DS . 'master_ledger.xlsx'; // Adjust this path to match your file
        } elseif ($category == 'bank_statement') {
            $filePath = APP . 'webroot' . DS . 'uploads' . DS . 'bank_statements' . DS . 'master_ledger.xlsx'; // Adjust this path to match your file
        } else {
            $this->Session->setFlash(__d('webzash', 'Invalid category selected!'), 'default', array('class' => 'alert alert-danger'));
            return $this->redirect(array('action' => 'index'));
        }
    
        // Check if the file exists
        if (file_exists($filePath)) {
            // Set the appropriate headers to force the file download
            $this->response->file($filePath, array(
                'download' => true,
                'name' => basename($filePath), // Set the name of the file being downloaded
            ));
            return $this->response; // Return the response to trigger the download
        } else {
            // If file does not exist, show an error message
            $this->Session->setFlash(__d('webzash', 'The requested file does not exist!'), 'default', array('class' => 'alert alert-danger'));
            return $this->redirect(array('action' => 'index')); // Redirect back to the index page
        }
    }
    

    private function countFilesInDirectory($path) {
            if (!is_dir($path)) {
                return 0;
            }
            $files = array_diff(scandir($path), array('..', '.'));
            return count($files);
        }
    

    public function isAuthorized($user) {
        if ($this->action === 'index') {
            return $this->Permission->is_admin_allowed();
        }
        return parent::isAuthorized($user);
    }
}
