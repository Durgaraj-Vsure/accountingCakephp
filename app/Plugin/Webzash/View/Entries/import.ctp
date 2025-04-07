<?php
/**
 * The MIT License (MIT)
 *
 * Webzash - Easy to use web based double entry accounting software
 *
 * Copyright (c) 2014 Prashant Shah <pshah.mumbai@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
?>

<div class="entry import form">
    <?php
    echo $this->Form->create('Entry', array(
        'type' => 'file',
        'inputDefaults' => array(
            'div' => 'form-group',
            'wrapInput' => false,
            'class' => 'form-control',
        ),
    ));
    ?>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __d('webzash', 'Import %s Entries', $entrytype['Entrytype']['name']); ?></h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <?php echo $this->Form->input('import_file', array('type' => 'file', 'label' => __d('webzash', 'Select File (.xls, .xlsx, .csv)'))); ?>
                    <?php echo $this->Form->input('tag_id', array('type' => 'select', 'options' => $tag_options, 'label' => __d('webzash', 'Tag (optional)'))); ?>
                    
                    <div class="alert alert-warning">
                        <h4><?php echo __d('webzash', 'Instructions'); ?></h4>
                        <ol>
                            <li><?php echo __d('webzash', 'Download the template file and fill in your entries'); ?></li>
                            <li><?php echo __d('webzash', 'Format: Entry Number, Date (DD/MM/YYYY), Narration, followed by entries as Ledger Name, Dr/Cr, Amount (groups of 3)'); ?></li>
                            <li><?php echo __d('webzash', 'Each row represents one entry'); ?></li>
                            <li><?php echo __d('webzash', 'For each entry, the Dr and Cr totals must match'); ?></li>
                            <li><?php echo __d('webzash', 'Do not modify the header row'); ?></li>
                        </ol>
                    </div>
                    
                    <div class="form-group">
                        <?php
                        echo $this->Html->link(__d('webzash', 'Download Template'), 
                            array('plugin' => 'webzash', 'controller' => 'entries', 'action' => 'downloadImportTemplate', $entrytype['Entrytype']['label']),
                            array('class' => 'btn btn-info')
                        );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?php
        echo $this->Form->submit(__d('webzash', 'Import'), array(
            'div' => false,
            'class' => 'btn btn-primary'
        ));
        echo $this->Html->tag('span', '', array('class' => 'link-pad'));
        echo $this->Html->link(__d('webzash', 'Cancel'), array('plugin' => 'webzash', 'controller' => 'entries', 'action' => 'index'), array('class' => 'btn btn-default'));
        ?>
    </div>

    <?php echo $this->Form->end(); ?>
</div>