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

    public function index() {
        $this->set('title_for_layout', __d('webzash', 'File Upload'));

        if ($this->request->is('post')) {
            if (!empty($this->request->data['FileUpload']['file']['name'])) {
                // File upload logic
                $file = $this->request->data['FileUpload']['file'];

                // Check if the file is a valid upload
                if ($file['error'] === UPLOAD_ERR_OK) {
                    // Set the target file path for the uploaded file
                    $targetFile = $this->uploadsPath . basename($file['name']);

                    // Make sure the uploads directory exists
                    if (!is_dir($this->uploadsPath)) {
                        mkdir($this->uploadsPath, 0777, true); 
                    }

                    // Move the uploaded file to the target directory
                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                        $this->Session->setFlash(__d('webzash', 'File has been uploaded successfully!'), 'default', array('class' => 'alert alert-success'));
                        // $this->masterLedgerPath = $targetFile; // Update the file path for reading
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
        $uploadsCount = $this->countFilesInDirectory($this->uploadsPath);
        $processedCount = $this->countFilesInDirectory($this->processedPath);
        $failedCount = $this->countFilesInDirectory($this->failedPath);

        $this->set(compact('uploadsCount', 'processedCount', 'failedCount'));

        if (file_exists($this->masterLedgerPath)) {
            $spreadsheet = IOFactory::load($this->masterLedgerPath);
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
            $this->set('excelData', $rows);
        }
    }

    public function download() {
        // Set the path to the file you want to download
        $filePath = $this->masterLedgerPath;
    
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
