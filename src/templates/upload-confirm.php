<?php if(count($successPhotos) > 0) { ?>
  <?php if(count($failure) === 0) { ?>
    <h2>Your photos are finished uploading <small><a href="<?php $this->utility->safe($url); ?>">view all</a></small></h2>
  <?php } else { ?>
  <h2>Your photos are finished uploading, but <?php echo count($failure); ?> had problems. Now what?</h2>
  <?php } ?>
<?php } else { // no photos uploaded ?>
  <h2>None of your photos were uploaded</h2>
  <p>
    See below for more details. If you continue to have problems drop us a note on our mailing list <a href="mailto:support@trovebox.com">support@trovebox.com</a>.
  </p>
<?php } ?>

<?php if(count($successPhotos) > 0) { ?>
  <strong><span class="label label-success"><?php printf('%d %s %s', count($successPhotos), $this->utility->plural(count($successPhotos), 'photo', false), $this->utility->selectPlural(count($successPhotos), 'was', 'were', false)); ?> uploaded successfully.</span></strong>
  <div class="upload-preview success photo-grid">
  </div>
  <hr>
<?php } ?>

<?php if(count($failure) > 0) { ?>
  <strong><span class="label label-important"><?php printf('%d %s', count($failure), $this->utility->plural(count($failure), 'photo', false)); ?> could not be uploaded. Booo!</span></strong>
  <div>
    <ul>
      <?php foreach($failure as $name) { ?>
        <li><?php $this->utility->safe($name); ?></li>
      <?php } ?>
    </ul>
  </div>
<?php } ?>
