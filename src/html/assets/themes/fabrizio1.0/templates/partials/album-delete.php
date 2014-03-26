<div class="row">
  <div class="span12"><h4>Delete the album "<em><?php $this->utility->safe($album['name']); ?></em>"</h4></div>
</div>
<div class="row">
  <div class="span6">
    <p>
      <form class="albumDelete">
        <input type="hidden" name="id" value="<?php $this->utility->safe($album['id']); ?>">
        <button type="submit" class="btn btn-brand">Delete Album</button> or <a href="#" class="batchHide">cancel</a>
      </form>
  </div>
</div>
<a href="#" class="batchHide close" title="Close this dialog"><i class="icon-remove batchHide"></i></a>

