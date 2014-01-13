<h2>Enter your name or nickname</h2>
<form class="form-stacked uploadTokenDialog">
  <div class="control-group">
    <input type="text" name="identifier" placeholder="your name or nickname" class="input-large fixed">
    <div><small><i class="icon-info-sign"></i> Helps the account owner know these are your photos</small></div>
  </div>
  <div class="btn-toolbar">
    <button class="btn btn-brand wide">Continue</button>
  </div>
  <?php if(stripos(strtolower($_SERVER['HTTP_USER_AGENT']),'android') !== false) { ?>
    <div class="hidden-desktop altAndroidApp">
      <small>
        Or use our <a href="market://details?id=com.trovebox.android.app.collect&referrer=<?php $this->utility->safe($token); ?>">Android app</a>
      </small>
    </div>
  <?php } ?>
</form>
