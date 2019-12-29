<?php 
session_start();
session_destroy();
include ('idioma/es.php'); 
include ('server.php');
$title = WELCOME;
include ('header.php');
?>

<body class="bg-gray-900">

  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4"><?php echo WELCOME; ?></h1>
                  </div>
                  <form class="user" method="post" action="login.php">
                    <div class="form-group">
                      <?php include('errors.php'); ?>
                    </div>
                    <div class="form-group">
                      <input type="email" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address..." name="email" required>
                    </div>
                    <div class="invalid-feedback">
                      Please choose a username.
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password" name="password" required>
                    </div>
                      <?php select_clubes(); ?>
                    <div class="form-group">
                      <button type="submit" class="btn btn-primary btn-user btn-block" name="login_user"><?php echo LOGIN; ?></button>
                    </div>
                  </form>
                  <hr>
                  <div class="text-center">
                    <a class="small" href="forgot-password.php"><?php echo FORGOT; ?></a>
                  </div>
                  <div class="text-center">
                    <a class="small" href="register.php"><?php echo CREATE_ACOUNT; ?></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

</body>

</html>