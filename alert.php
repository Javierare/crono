<body>
    <div class="container h-100">
        <div class="row align-items-center h-100">
            <div class="col-6 mx-auto">
                <div class="alert alert-<?php echo $alert['type']; ?> border-2 border-danger mt-5" role="alert">
                    <h4 class="alert-heading"><?php echo $alert['title']; ?></h4>
                    <p><?php echo $alert['body']; ?></p>
                    <!-- Se puede colocar info extra -->
                    <div class="text-right">
                        <a href="<?php echo $alert['location']; ?>" class="btn btn-<?php echo $alert['type']; ?>">Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>