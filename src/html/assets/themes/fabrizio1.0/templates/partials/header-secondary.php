          <?php if($this->utility->isActiveTab('albums')) { ?>
            <?php if($this->user->isAdmin()) { ?>
              <li class="separator-left batch-meta"></li>
            <?php } ?>
          <?php } else if($this->utility->isActiveTab('photo')) { ?>
            <?php if($this->user->isAdmin() || $this->config->site->allowOriginalDownload == 1) { ?>
              <li class="separator-left"><a href="#" class="triggerDownload"><i class="icon-download triggerDownload"></i> Download</a></li>
            <?php } ?>
            <?php if($this->user->isAdmin()) { ?>
              <li><a href="#" class="triggerShare"><i class="icon-share-alt triggerShare"></i> Share</a></li>
            <?php } ?>
          <?php } else if($this->utility->isActiveTab('photos')) { ?>
            <?php if($this->user->isAdmin()) { ?>
              <li class="batch separator-left"><a href="#" class="selectAll"><i class="icon-pushpin"></i> Select all</a></li>
              <li class="batch dropdown batch-meta"></li>
            <?php } ?>
            <?php if($this->utility->isActiveTab('photos-album') && ($this->user->isAdmin() || $this->permission->canUpload($this->utility->getAttributeFromPath('album')))) { ?>
              <li class="separator-left">Album:</li>
              <li><a href="#" class="triggerShare" title="Share all the photos in this album"><i class="icon-share-alt triggerShare"></i> Share</a></li>
              <li><a href="#" class="albumInviteUploaders" data-id="<?php $this->utility->safe($this->utility->getAttributeFromPath('album')); ?>" title="Get a Collect URL so others can send you photos"><i class="icon-link"></i> Collect URL</a></li>
            <?php } ?>
          <?php } elseif($this->utility->isActiveTab('upload') && $this->user->isAdmin()) {?>
            <li class="separator-left">Mobile apps </li>
            <li><a href="http://bit.ly/trovebox-for-iphone" title="Download our Trovebox for iOS"><i class="icon-apple"></i> iPhone / iPad</a></li>
            <li><a href="http://bit.ly/trovebox-for-android" title="Download Trovebox for Android"><i class="icon-android"></i> Android</a></li>
          <?php } ?>
