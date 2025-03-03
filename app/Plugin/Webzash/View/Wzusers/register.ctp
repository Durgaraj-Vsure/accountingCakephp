<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4" style="max-width: 800px; width: 100%;">
        <div class="row">
            <!-- Registration Form Section -->
            <div class="col-md-6">
                <div class="card-body">
                    <h3 class="text-center mb-4"><?php echo __d('webzash', 'Register'); ?></h3>
                    <?php
                        if ($registration) {
                            echo $this->Form->create('Wzuser', array(
                                'inputDefaults' => array(
                                    'div' => 'form-group',
                                    'wrapInput' => false,
                                    'class' => 'form-control',
                                ),
                                'class' => 'needs-validation'
                            ));

                            echo '<div class="mb-3">';
                            echo $this->Form->input('username', array('label' => __d('webzash', 'Username'), 'class' => 'form-control'));
                            echo '</div>';

                            echo '<div class="mb-3">';
                            echo $this->Form->input('password', array('label' => __d('webzash', 'Password'), 'class' => 'form-control'));
                            echo '</div>';

                            echo '<div class="mb-3">';
                            echo $this->Form->input('fullname', array('label' => __d('webzash', 'Fullname'), 'class' => 'form-control'));
                            echo '</div>';

                            echo '<div class="mb-3">';
                            echo $this->Form->input('email', array('type' => 'email', 'label' => __d('webzash', 'Email'), 'class' => 'form-control'));
                            echo '</div>';

                            echo '<div class="d-grid">';
                            echo $this->Form->submit(__d('webzash', 'Register'), array(
                                'div' => false,
                                'class' => 'btn btn-primary btn-block'
                            ));
                            echo '</div>';

                            echo '<div class="text-center mt-3">';
                            echo $this->Html->link(__d('webzash', 'Login'), array('plugin' => 'webzash', 'controller' => 'wzusers', 'action' => 'login'), array('class' => 'btn btn-link text-decoration-none'));
                            echo '</div>';

                            echo $this->Form->end();
                        } else {
                            echo '<h4 class="text-center">' . __d('webzash', 'Sorry, user registration is disabled.') . '</h4><br />';

                            echo '<div class="form-group text-center">';
                            echo $this->Html->link(__d('webzash', 'Login'), array('plugin' => 'webzash', 'controller' => 'wzusers', 'action' => 'login'), array('class' => 'btn btn-primary'));
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>

            <!-- Image Section -->
            <div class="col-md-6">
				<?php echo $this->Html->image('Webzash.register.jpg', ['alt' => 'register', 'height' => '400', 'width' => '500']); ?>
            </div>
        </div>
    </div>
</div>
