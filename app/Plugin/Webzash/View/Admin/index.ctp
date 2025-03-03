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
<?php
$this->start('css');
echo $this->Html->css('Webzash.drag-drop'); // The path relative to webroot
$this->end();
?>
<div class="container mt-5">
    <div class="row g-4">
        <?php
        $cards = [
            ['Create Account', 'Create a new account', ['plugin' => 'webzash', 'controller' => 'wzaccounts', 'action' => 'create'], 'fas fa-user-plus', 'bg-create'],
            ['Manage Accounts', 'Manage existing accounts', ['plugin' => 'webzash', 'controller' => 'wzaccounts', 'action' => 'index'], 'fas fa-folder-open', 'bg-manage'],
            ['Manage Users', 'Manage users and permissions', ['plugin' => 'webzash', 'controller' => 'wzusers', 'action' => 'index'], 'fas fa-users', 'bg-users'],
            ['General Settings', 'General application settings', ['plugin' => 'webzash', 'controller' => 'wzsettings', 'action' => 'edit'], 'fas fa-cogs', 'bg-settings'],
            ['System Information', 'General system information', ['plugin' => 'webzash', 'controller' => 'wzsettings', 'action' => 'sysinfo'], 'fas fa-info-circle', 'bg-info'],
        ];

        foreach ($cards as $card) {
            echo '<div class="col-md-6 d-flex justify-content-center">';
            echo '<a href="' . $this->Html->url($card[2]) . '" class="webzash-card-btn ' . $card[4] . '">';
            echo '<i class="' . $card[3] . '"></i>';
            echo '<h5>' . __d('webzash', $card[0]) . '</h5>';
            echo '<p>' . __d('webzash', $card[1]) . '</p>';
            echo '</a>';
            echo '</div>';
        }
        ?>
    </div>
</div>

