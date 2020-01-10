<?php 
include('idioma/es.php'); 
include('server.php');
$title = WELCOME;
include('header.php');
?>

<body class="bg-gray-900">
 
 <!-- Lanza el modal -->
  <?php if(isset($modal)) include('modal.php'); ?>
  <!-- Fin del modal -->

  <div class="container">

    <div class="card o-hidden border-0 shadow-lg my-5">
      <div class="card-body p-0">
        <!-- Nested Row within Card Body -->
        <div class="row">
          <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
          <div class="col-lg-7">
            <div class="p-5">
              <div class="text-center">
                <h1 class="h4 text-gray-900 mb-4"><?php echo RESETPASS; ?></h1>
              </div>
              <form class="user" method="post">
                <div class="form-group">
                  <?php include('errors.php'); ?>
                </div>
                <div class="form-group">
                  <input type="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password" name="password_1" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" title="Mayúscula, minúscula y número" required>
                </div>
                <div class="form-group">
                  <input type="password" class="form-control form-control-user" id="exampleRepeatPassword" placeholder="Repeat Password" name="password_2" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" title="Mayúscula, minúscula y número" required>
                </div>
                <input type="hidden" name="fp_code" value="<?php echo $_REQUEST['fp_code']; ?>"/>
                <input type="hidden" name="prefix" value="<?php echo $_REQUEST['prefix']; ?>"/>
                <div class="form-group">
                  <button type="submit" class="btn btn-primary btn-user btn-block" name="reset_password"><?php echo RESETPASS; ?></button>
                </div>
              </form>
              <hr>
              <div class="text-center">
                <a class="small" href="register.php"><?php echo CREATE_ACOUNT; ?></a>
              </div>
              <div class="text-center">
                <a class="small" href="login.php"><?php echo LETSLOGIN; ?></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

</body>

</html>