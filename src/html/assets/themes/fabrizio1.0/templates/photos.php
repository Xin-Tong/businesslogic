<div class="row">
  <div class="span12 photo-grid <?php if(isset($album)) { ?>is-album<?php } ?>">
    <?php if(isset($album) && !empty($album)) { ?>
    <h4>
      <i class="icon-th-large"></i>
      <?php $this->utility->safe($album['name']); ?>
      <small>
        (
          <?php number_format($this->utility->safe($album['count'])); ?> photos
          <?php if($this->permission->canUpload($album['id'])) { ?>
            <span class="hide"> | <a href="#" class="shareAlbum share trigger" data-id="<?php $this->utility->safe($album['id']); ?>" title="Share this album"><i class="icon-share"></i> Share</a></span>
          <?php } ?>
          &middot; <i class="icon-sort-by-order" title="Photos sorted oldest taken to newest"></i>
        )
      </small>
    </h4>
    <?php } else if(isset($tags)) { ?>
      <h4><i class="icon-tags"></i> <?php $this->utility->safe(implode(', ', $tags)); ?> <small>(<?php number_format($this->utility->safe($photos[0]['totalRows'])); ?> photos &middot; <i class="icon-sort-by-order-alt" title="Photos sorted newest uploaded to oldest"></i>)</small></h4>
    <?php } else { ?>
      <h4><i class="icon-picture"></i> Gallery <small>( <?php number_format($this->utility->safe($photos[0]['totalRows'])); ?> photos &middot; <i class="icon-sort-by-order-alt" title="Photos sorted newest uploaded to oldest"></i> )</small></h4>
    <?php } ?>
    <?php if(!empty($photos)) { ?>
      <script> var initData = <?php echo json_encode($photos); ?>; var filterOpts = <?php echo json_encode($options); ?>;</script>
    <?php } else { ?>
      <?php if($this->user->isAdmin()) { ?>
        <h4>No photos to show.</h4>  
        <p>
          It's easy to start uploading photos. Head over to the <a href="/photos/upload">upload</a> page to get started.
        </p>
      <?php } else { ?>
        <h4>This user hasn't uploaded any photos, yet.</h4>  
        <p>
          You should give them a nudge to get started! If this is your site then you can <a href="/user/login?r=<?php $this->utility->safe(urlencode($_SERVER['REQUEST_URI'])); ?>">log in</a>.
        </p>
      <?php } ?>
    <?php } ?>
  </div>
</div>
<?php if(!empty($photos)) { ?>
  <div class="row">
    <div class="span12 loadMoreContainer">
      <button class="btn btn-theme-secondary loadMore loadMorePhotos hide"><i></i> Load more</button>
    </div>
  </div>
<?php } ?>
