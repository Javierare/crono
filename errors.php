<?php  if (count($errors) > 0) : ?>
    <div class="alert alert-danger">
        <?php 
            // Si tenemos datos de sesión se las añadimos al log como cabecera
            $pre = ''; $head = '';
            if(isset($_SESSION['prefix'])){
                $pre = $_SESSION['prefix'];
                $head = " | ".implode(" | ", $_SESSION);
            } 
            foreach ($errors as $page => $error){ 
                echo $error.'<br/>';
                error_log(date("Y-m-d H:i:s").$head.PHP_EOL, 3, FISPATH."/log/".$pre."error_log.php");
                error_log($page.': '.$error.PHP_EOL, 3, FISPATH."/log/".$pre."error_log.php"); // Un archivo log por club 
             } 
          ?>
    </div>
<?php  endif ?>