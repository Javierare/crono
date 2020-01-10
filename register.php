<?php 
include('idioma/es.php'); 
include('server.php');
$title = CREATE_ACOUNT;
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
                <h1 class="h4 text-gray-900 mb-4"><?php echo CREATE_ACOUNT; ?></h1>
              </div>
              <form class="user" method="post" action="register.php">
                <div class="form-group">
                  <?php include('errors.php'); ?>
                </div>
                <?php select_clubes(); ?>
                <hr>
                <div class="form-group">
                  <input type="text" class="form-control form-control-user" id="exampleFirstName" placeholder="Nombre de usuario" name="username" required>
                </div>
                <div class="form-group">
                  <input type="email" class="form-control form-control-user" id="exampleInputEmail" placeholder="Dirección de Email" name="email" required>
                </div>
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Contraseña" name="password_1" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" title="Mayúscula, minúscula y número" required>
                  </div>
                  <div class="col-sm-6">
                    <input type="password" class="form-control form-control-user" id="exampleRepeatPassword" placeholder="Repite Contraseña" name="password_2" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" title="Mayúscula, minúscula y número" required>
                  </div>
                </div>
                <div class="form-group">
                  <button type="submit" class="btn btn-primary btn-user btn-block" name="reg_user"><?php echo REGISTER; ?></button>
                </div>
              </form>
              <hr>
              <div class="text-center">
                <a class="small" href="forgot-password.php"><?php echo FORGOT; ?></a>
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