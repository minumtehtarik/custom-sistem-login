<div class="container">

    <div class="card o-hidden border-0 shadow-lg my-5 col-lg-7 mx-auto">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">

                <div class="col-lg">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                        </div>
                        <form class="user" method="post" action="<?= base_url('auth/registration'); ?>">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control form-control-user" id="name" placeholder="Full name" value="<?= set_value('name'); ?>">
                                <!-- set_value ada di libary Ci untuk me set 'name' agar form inputnya tidak kosong ketika terjadi fail/kesalahan dalam pengisisan -->
                                <!-- ada di library form_validation eror -->
                                <?= form_error('name', '<small class="text-danger pl-3">', '</small>'); ?>
                            </div>
                            <div class="form-group">
                                <input type="text" name="email" class="form-control form-control-user" id="email" placeholder="Email Address" value="<?= set_value('email'); ?>">
                                <!-- ada di library form_validation eror -->
                                <?= form_error('email', '<small class="text-danger pl-3">', '</small>'); ?>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="password" name="password1" class="form-control form-control-user" id="password1" placeholder="Password">
                                    <?= form_error('password1', '<small class="text-danger pl-3">', '</small>'); ?>
                                </div>
                                <div class="col-sm-6">
                                    <input type="password" name="password2" class="form-control form-control-user" id="password2" placeholder="Repeat Password">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Register Account
                            </button>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a class="small" href="<?= base_url('auth/forgotpassword'); ?>">Forgot Password?</a>
                        </div>
                        <div class="text-center">
                            <a class="small" href="<?= base_url('auth'); ?>">Already have an account? Login!</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>