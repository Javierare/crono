<!-- The Modal -->
<div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="<?php echo $modal['hidden']; ?>">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title"><?php echo $modal['title']; ?></h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <?php echo $modal['body']; ?>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <?php if(isset($modal['cancel'])): ?>
        <button class="btn btn-secondary" type="button" data-dismiss="modal"><?php echo CANCEL; ?></button>
        <?php endif; ?>
        <a class="btn btn-primary" href="<?php echo $modal['location']; ?>"><?php echo OK; ?></a>
      </div>

    </div>
  </div>
</div>