<?php
// Set the page title for the layout
$this->set('title_for_layout', __d('webzash', 'File Upload'));
?>
<!-- In another action view (another_action.ctp) -->
<?php
$this->start('css');
echo $this->Html->css('Webzash.drag-drop'); // The path relative to webroot
$this->end();
?>

<div class="container d-flex flex-column align-items-center justify-content-center vh-100">
<div class="col-md-6">
    <?php
    // Display flash messages (success or error)
    echo $this->Session->flash('default', array('class' => 'alert alert-info text-center'));

    // Create the form for uploading a file
    echo $this->Form->create('FileUpload', array(
        'type' => 'file',
        'url' => array('controller' => 'file_upload', 'action' => 'index'),
        'class' => 'card p-4 shadow-lg text-center'
    ));
    ?>

    <h3 class="mb-3"><?php echo __d('webzash', 'Upload PDF File'); ?></h3>

    <!-- Category select input -->
    <div class="form-group">
        <?php
        echo $this->Form->input('category', array(
            'type' => 'select',
            'options' => array(
                '' => 'Select Category',
                'sales' => 'Sales',
                'bank_statement' => 'Bank Statement',
            ),
            'label' => 'Select Category',
            'required' => true
        ));
        ?>
    </div>

    <!-- File input -->
    <div class="form-group">
        <?php
        echo $this->Form->input('file', array(
            'type' => 'file',
            'label' => false,
            'class' => 'form-control mb-3',
            'required' => true
        ));
        ?>
    </div>

    <?php echo $this->Form->end(array('label' => 'Upload File', 'class' => 'btn btn-success w-100')); ?>

</div>


<div class="col-md-6 text-center mt-3">
    <?php
    echo $this->Form->create('FileUpload', array(
        'type' => 'post',
        'url' => array('controller' => 'file_upload', 'action' => 'download'),
        'class' => 'card p-4 shadow-lg text-center'
    ));
    ?>

    <h3 class="mb-3"><?php echo __d('webzash', 'Select Category for Download'); ?></h3>

    <div class="form-group">
        <?php
        echo $this->Form->input('category', array(
            'type' => 'select',
            'options' => array(
                '' => 'Select Category',
                'sales' => 'Sales',
                'bank_statement' => 'Bank Statement',
            ),
            'label' => 'Select Category',
            'required' => true
        ));
        ?>
    </div>

    <?php echo $this->Form->button(__('Download File'), array('class' => 'btn btn-primary w-100')); ?>

    <?php echo $this->Form->end(); ?>
</div>

</div>

<div class="file-counts-container">
    <div class="file-counts-card uploads">
        <h4>Uploads</h4>
        <span class="count-number" id="uploadsCount"><?php echo $uploadsCount; ?></span>
    </div>
    <div class="file-counts-card processed">
        <h4>Processed</h4>
        <span class="count-number" id="processedCount"><?php echo $processedCount; ?></span>
    </div>
    <div class="file-counts-card failed">
        <h4>Failed</h4>
        <span class="count-number" id="failedCount"><?php echo $failedCount; ?></span>
    </div>
</div>
<!-- Excel Data Table -->
<?php if (isset($excelData) && !empty($excelData)): ?>
 
<div class="row mt-4">
    <div class="col-lg-12">
        <h3 class="text-center">Excel File Data</h3>
        <div class="table-responsive" style="overflow-x: auto; white-space: nowrap;">
            <table id="excelTable" class="table table-striped table-bordered nowrap" style="width:100%">
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
</div>

<?php endif; ?>

<!-- Include DataTables JS and CSS -->
<?php
echo $this->Html->css('https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css');
echo $this->Html->script('https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js');
echo $this->Html->css('https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
echo $this->Html->script('https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js');
?>

<?php
echo $this->Html->scriptBlock('
$(document).ready(function() {
    $("#excelTable").DataTable({
        "paging": true,        // Enable pagination
        "searching": true,     // Enable search box
        "responsive": true,    // Enable responsive mode
        "lengthChange": false, // Disable changing page length
        "autoWidth": false,    // Prevent columns from being too wide
        "columnDefs": [
            { "width": "20%", "targets": 0 } // Adjust column widths as needed
        ]
    });
});
');
?>

