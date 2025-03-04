<?php
$this->start('css');
echo $this->Html->css('Webzash.drag-drop'); // The path relative to webroot
$this->end();
?>
<div class="container mt-5">
    <!-- First row with 3 cards -->
    <div class="row g-4">
        <?php
        $cards = [
            ['Create Account', 'Create a new account', ['plugin' => 'webzash', 'controller' => 'wzaccounts', 'action' => 'create'], 'fas fa-user-plus', 'bg-create'],
            ['Manage Accounts', 'Manage existing accounts', ['plugin' => 'webzash', 'controller' => 'wzaccounts', 'action' => 'index'], 'fas fa-folder-open', 'bg-manage'],
            ['Manage Users', 'Manage users and permissions', ['plugin' => 'webzash', 'controller' => 'wzusers', 'action' => 'index'], 'fas fa-users', 'bg-users'],
        ];

        foreach ($cards as $card) {
            echo '<div class="col-md-4 d-flex justify-content-center">'; // Use col-md-4 to fit 3 cards per row
            echo '<a href="' . $this->Html->url($card[2]) . '" class="webzash-card-btn ' . $card[4] . '">';
            echo '<i class="' . $card[3] . '"></i>';
            echo '<h5>' . __d('webzash', $card[0]) . '</h5>';
            echo '<p>' . __d('webzash', $card[1]) . '</p>';
            echo '</a>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- Second row with 2 cards, centered -->
    <div class="row g-4 justify-content-center">
        <div class="col-md-2"></div> 
        <?php
        $cards = [
            ['General Settings', 'General application settings', ['plugin' => 'webzash', 'controller' => 'wzsettings', 'action' => 'edit'], 'fas fa-cogs', 'bg-settings'],
            ['System Information', 'General system information', ['plugin' => 'webzash', 'controller' => 'wzsettings', 'action' => 'sysinfo'], 'fas fa-info-circle', 'bg-info'],
        ];

        foreach ($cards as $card) {
            echo '<div class="col-md-4 d-flex justify-content-center">'; // Use col-md-5 to fit 2 cards per row with some spacing
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
