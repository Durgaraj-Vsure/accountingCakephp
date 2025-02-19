<?php
// Set the page title for the layout
$this->set('title_for_layout', __d('webzash', 'File Upload'));
?>

<?php
// Display flash messages (success or error)
echo $this->Session->flash('default', array('class' => 'alert alert-info'));

// Create the form for uploading a file (Only one form now)
echo $this->Form->create('FileUpload', array(
    'type' => 'file', // Set the form type to file for file uploads
    'url' => array('controller' => 'file_upload', 'action' => 'index') // The form will submit to the index action of the FileUploadController
));

// File input field
echo $this->Form->input('file', array(
    'type' => 'file',
    'label' => __d('webzash', 'Select an PDF File to upload'),
    'required' => true
));

// Submit button
echo $this->Form->end('Upload File');
?>
<div class="submit-area">
        <a href="<?php echo $this->Html->url(array('controller' => 'file_upload', 'action' => 'download')); ?>" class="btn btn-primary">
            Download Excel File
        </a>
</div>
<!-- Excel Data Table -->
<?php if (isset($excelData) && !empty($excelData)): ?>
    <div class="row mt-4">
        <div class="col-lg-12">
            <h3>Excel File Data</h3>
            <table id="excelTable" class="table table-striped table-bordered table-responsive">
                <thead>
                    <tr>
                        <?php foreach ($excelData[0] as $header): ?>
                            <th><?php echo h($header); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($excelData as $key => $row): ?>
                        <?php if ($key > 0): // Skip header row ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?php echo h($cell); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Include DataTables JS and CSS -->
<?php
echo $this->Html->css('https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
echo $this->Html->script('https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js');
?>

<?php
echo $this->Html->scriptBlock('
    // Initialize DataTable for the uploaded data
    $(document).ready(function() {
        $("#excelTable").DataTable({
            "paging": true,       // Enable pagination
            "searching": true,    // Enable search box
            "responsive": true,   // Make it responsive on different screen sizes
            "lengthChange": false // Disable changing page length
        });
    });
');
?>

