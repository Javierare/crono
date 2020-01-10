<?php 
include('idioma/es.php'); 
include('server.php');
$title = FORGOT;
include('header.php');
?>

<body class="bg-gray-900">
  <!-- Lanza el modal -->
  <?php if(isset($modal)) include('modal.php'); ?>
  <!-- Fin del modal -->
  
  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-2"><?php echo FORGOT; ?></h1>
                    <p class="mb-4"><?php echo SENDYOULINK; ?></p>
                  </div>
                  <form class="user" action="forgot-password.php" method="post">
                    <div class="form-group">
                      <?php include('errors.php'); ?>
                    </div>
                    <div class="form-group">
                      <input type="email" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Dirección de Email" name="email" required>
                    </div>
                    <?php select_clubes(); ?>
                    <div class="form-group">
                      <button type="submit" class="btn btn-primary btn-user btn-block" name="forgot_submit"><?php echo SENDLINK; ?></button>
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

    </div>

  </div>

</body>

</html>