<?php 
session_start();

include ('idioma/es.php'); 
include ('function_user.php');
//include('server.php');
$title = WELCOME;
include ('header.php');
?>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <?php //include ('sidebar.php'); ?> 
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
      <!-- Main Content -->
      <div id="content">
        <!-- Topbar -->
        <?php include ('topbar.php'); ?>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">
          <!-- Page Heading -->
          <h1 class="h3 mb-4 text-gray-800">Panel de administración</h1>

          <div class="row">
            <div class="col-lg-6">
            <?php if(!$cars): ?>
              <div class="alert alert-info" role="alert">
                <strong>Guarda un nuevo coche</strong>
                <button type="button" class="close" data-dismiss="no" aria-label="Close">
                  <span aria-hidden="true"><i class="fa fa-info-circle"></i></span>
                </button>
                <p>Deberás tener al menos un coche guardado para ver los rallies abiertos y poder inscribirte en ellos.</p>
              </div>
              <?php endif; ?>
              <!-- Basic Card: Mis Coches -->
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Mis coches</h6>
                </div>
                <div class="card-body">

                <?php if(count($errors)) include ('errors.php'); ?>

                <?php foreach($cars as $car): ?>
                <div class="card shadow mb-4">
                  <!-- Card Header - Accordion -->
                  <a href="#collapseCard<?php echo $car['id']; ?>" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseCard<?php echo $car['id']; ?>">
                  <h6 class="m-0 font-weight-bold text-gray-800"><?php echo $car['body'].' ('.$car['chassis'].')'; ?></h6>
                  </a>
                  <!-- Card Content - Collapse -->
                  <div class="collapse" id="collapseCard<?php echo $car['id']; ?>">
                    <div class="card-body">
                       <form action="user.php" method="post">
                        <div class="form-group">
                          <label for="chassis">Chasis:</label>
                          <input type="chassis" class="form-control" placeholder="Enter chassis" name="chassis" value="<?php echo $car['chassis']; ?>">
                        </div>
                        <div class="form-group">
                          <label for="body">Carroceria:</label>
                          <input type="text" class="form-control" placeholder="Enter body" name="body" value="<?php echo $car['body']; ?>">
                        </div>
                        <input type="hidden" name="idcar" value="<?php echo $car['id']; ?>">
                        <div class="text-center">
                          <button type="submit" class="btn btn-primary" name="update_car">Guardar</button>
                          <button type="submit" class="btn btn-danger" name="delete_car">Eliminar coche</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>

                <!-- Inscribir coche -->
                <div class="card shadow mb-4">
                  <!-- Card Header - Accordion -->
                  <a href="#nuevoCoche" class="d-block card-header py-3  bg-gray-700" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseCardExample">
                    <h6 class="m-0 font-weight-bold text-gray-100">Guardar nuevo coche</h6>
                  </a>
                  <!-- Card Content - Collapse -->
                  <div class="collapse" id="nuevoCoche" style="">
                    <div class="card-body">
                      <form action="user.php" method="post">
                        <div class="form-group">
                          <label for="chassis">Chasis:</label>
                          <input type="chassis" class="form-control" placeholder="Enter chassis" name="chassis" required>
                        </div>
                        <div class="form-group">
                          <label for="body">Carroceria:</label>
                          <input type="text" class="form-control" placeholder="Enter body" name="body" required>
                        </div>
                        <div class="text-center">
                          <button type="submit" class="btn btn-primary" name="add_car">Guardar</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <!-- Fin Inscribir coche -->
                    
                </div>
              </div>
              <!-- End Basic Card: Mis Coches -->
            </div>
          

            <!-- Open Rallies -->
            <div class="col-lg-6">
              <?php if($open_rallies AND $cars): ?>
                <div class="alert alert-dark" role="alert">
                <strong> Rallies con inscripciones abiertas</strong>
                <button type="button" class="close" data-dismiss="no" aria-label="Close">
                  <span aria-hidden="true"><i class="fa fa-flag-checkered"></i></span>
                </button>
                  
                </div>
              <?php foreach($open_rallies as $open_rally): ?>
                <!-- Basic Card: Open Rally -->
                <div class="card shadow mb-4">
                  <div class="card-header py-3">
                  <div class="row">
                    <div class="col-md-8"><h6 class="m-0 font-weight-bold text-primary"><?php echo $open_rally['name']; ?></h6></div>
                    <div class="col-md-4 text-right"><?php echo convertDate($open_rally['date']); ?></div>
                  </div>
                  </div>
                  <div class="card-body">

                    <?php if($unsigned_cars = unsigned_cars($open_rally, $cars)): ?>
                      <?php if(signed_granted($open_rally)): ?>
                        <div class="alert alert-info" role="alert">
                          <strong>Puedes inscribirte en este rally</strong>
                          <button type="button" class="close" data-dismiss="no" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-info-circle"></i></span>
                          </button>
                              <p>Estate atento a inscribir tu coche en la categoría correcta.</p>
                        </div>
                        <!-- Inscribir coche -->
                        <div class="card shadow mb-4">
                          <!-- Card Header - Accordion -->
                          <a href="#signCar<?php echo $open_rally['id']; ?>" class="d-block card-header py-3  bg-gray-700" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="signCar<?php echo $open_rally['id']; ?>">
                            <h6 class="m-0 font-weight-bold text-gray-100">Inscribir nuevo coche</h6>
                          </a>
                          <!-- Card Content - Collapse -->
                          <div class="collapse" id="signCar<?php echo $open_rally['id']; ?>" style="">
                            <div class="card-body">
                            <?php include ('errors.php'); ?>
                              <form action="user.php" method="post">
                                <div class="form-group">
                                  <label for="category">Categoría:</label>
                                  <select name="category" id="category">
                                    <?php options_categories($open_rally); ?>
                                  </select>
                                </div>
                                <div class="form-group">
                                  <label for="carid">Coches disponibles:</label>
                                  <select name="carid" id="carid">
                                    <?php options_cars($unsigned_cars); ?>
                                  </select>
                                </div>
                                <input type="hidden" name="idrally" value="<?php echo $open_rally['id']; ?>">
                                <div class="text-center"></divr>
                                  <button type="submit" class="btn btn-primary" name="signup">Inscribir</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                        <!-- Fin Inscribir coche -->
                      <?php endif; ?>
                    <?php endif; ?>
                  
                  <?php list_signed_cars($open_rally, $cars); ?>
                  
                  </div>
                </div>
                <!-- End Basic Card: Open Rally -->
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <!-- End Open Rallies -->
          </div>

        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <?php include('footer.php'); ?>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

</body>

</html>