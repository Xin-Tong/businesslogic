<div class="row">
  <div class="span12"><h4>Invite others to upload to <em><?php $this->utility->safe($album['name']); ?></em></h4></div>
</div>
<div class="row">
  <div class="span6">
    <p>Trovebox Collect makes it easy for others to send you photos.</p>
    <p><button class="btn btn-brand copyToClipboard addSpinner" data-clipboard-text="<?php $this->utility->safe($token['link']); ?>">Copy link to clipboard</button></p>
    <p>
      You can email, text or instant message <a href="<?php $this->utility->safe($token['link']); ?>">this link</a> to anyone you'd like to upload photos into this album.
    </p>
    <p>
    </p>
    <p>
      <i class="icon-info-sign"></i> 
      <?php if($token['dateExpires'] == 0) { ?>
        This link never expires.
      <?php } else { ?>
        This link expires in <?php echo intval(($token['dateExpires']-time())/86400); ?> days. <small>(<?php echo $this->utility->dateLong($token['dateExpires']); ?>)</small>
      <?php } ?>
    </p>
  </div>
</div>
<a href="#" class="batchHide close" title="Close this dialog"><i class="icon-remove batchHide"></i></a>
