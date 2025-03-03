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
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="row w-100">
        <!-- Left Column: Login Form -->
        <div class="col-md-6">
            <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
                <div class="card-body">
                    <h3 class="text-center mb-4">Login</h3>
                    <?php
                        if ($first_login) {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                            echo __d('webzash', 'Since this is your first time, you can login with username as "admin" and password as "admin". Please change your password after login.');
                            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            echo '</div>';
                        } else if ($default_password) {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                            echo __d('webzash', 'Warning! Password still not updated for "admin" user. Please change your password after login.');
                            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            echo '</div>';
                        }

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

                        echo '<div class="d-grid">';
                        echo $this->Form->submit(__d('webzash', 'Login'), array(
                            'div' => false,
                            'class' => 'btn btn-primary btn-block'
                        ));
                        echo '</div>';

                        echo $this->Form->end();
                    ?>
                    <div class="text-center mt-3">
                        <?php
                            echo $this->Html->link(__d('webzash', 'Register'), array('plugin' => 'webzash', 'controller' => 'wzusers', 'action' => 'register'), array('class' => 'text-decoration-none'));
                            echo ' | ';
                            echo $this->Html->link(__d('webzash', 'Forgot Password'), array('plugin' => 'webzash', 'controller' => 'wzusers', 'action' => 'forgot'), array('class' => 'text-decoration-none'));
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Image -->
        <div class="col-md-6">
		<?php echo $this->Html->image('Webzash.login.jpg', ['alt' => 'loginImage', 'height' => '400', 'width' => '400']); ?>
		
		</div>
    </div>
</div>

