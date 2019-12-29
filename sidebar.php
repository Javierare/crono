<?php 
$open_rallies = consulta_multi("rallies", "open=1 and deputy=0");
$closed_rallies = consulta_multi("rallies", "open=0 and deputy=0");
$deputy_rallies = consulta_multi("rallies", "deputy=1");
?>
<ul class="navbar-nav bg-gray-900 sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item">
        <a class="nav-link" href="index.html">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Nuevos
      </div>

      <!-- Nav Item - Pages Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
          <i class="fas fa-fw fa-cog"></i>
          <span>Configurar</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Configuraciones:</h6>
            <a class="collapse-item" href="categories.php">Categorías</a>
            <a class="collapse-item" href="penalties.php">Penalizaciones</a>
          </div>
        </div>
      </li>

      <!-- Nav Item - Utilities Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
          <i class="fas fa-fw fa-wrench"></i>
          <span>Rallies nuevos</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="new_rally.php">Crear rally</a>
            <?php if($open_rallies): ?>  
            <div class="collapse-divider"></div>
            <h6 class="collapse-header">Inscripciones Abiertas:</h6>
            <?php endif; ?>
            <?php foreach($open_rallies as $open_rally): ?>
            <a class="collapse-item" href="new_rallies.php?collapseCardlist=<?php echo $open_rally['id']; ?>#card<?php echo $open_rally['id']; ?>"><?php echo $open_rally['name']; ?></a>
            <?php endforeach; ?>
            <?php if($closed_rallies): ?>
            <div class="collapse-divider"></div>
            <h6 class="collapse-header">Inscripciones Cerradas:</h6> 
            <?php endif; ?>
            <?php foreach($closed_rallies as $closed_rally): ?>
            <a class="collapse-item" href="new_rallies.php?collapseCardlist=<?php echo $closed_rally['id']; ?>#card<?php echo $closed_rally['id']; ?>"><?php echo $closed_rally['name']; ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Finalizados
      </div>

      <!-- Nav Item - Pages Collapse Menu -->
      <li class="nav-item">
        <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
          <i class="fas fa-fw fa-folder"></i>
          <span>Rallies finalizados</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <!-- <h6 class="collapse-header">Login Screens:</h6> -->
            <?php foreach($deputy_rallies as $deputy_rally): ?>
            <a class="collapse-item" href="rally.php?idrally=<?php echo $deputy_rally['id'] ?>"><?php echo $deputy_rally['name'] ?></a>
            <?php endforeach; ?>
            <div class="collapse-divider"></div>
            <h6 class="collapse-header">Other Pages:</h6>
            <a class="collapse-item" href="404.html">404 Page</a>
            <a class="collapse-item" href="blank.html">Blank Page</a>
          </div>
        </div>
      </li>

      <!-- Nav Item - Charts -->
      <li class="nav-item">
        <a class="nav-link" href="charts.html">
          <i class="fas fa-fw fa-chart-area"></i>
          <span>Charts</span></a>
      </li>

      <!-- Nav Item - Tables -->
      <li class="nav-item">
        <a class="nav-link" href="tables.html">
          <i class="fas fa-fw fa-table"></i>
          <span>Tables</span></a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>