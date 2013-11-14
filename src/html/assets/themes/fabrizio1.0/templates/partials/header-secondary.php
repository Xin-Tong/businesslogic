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
            <?php if($this->utility->isActiveTab('photos-album') && ($this->user->isAdmin()/* || $this->permission->canUpload($album)*/)) { ?>
              <li class="separator-left">Album:</li>
              <li><a href="#" class="triggerShare"><i class="icon-share-alt triggerShare"></i> Share</a></li>
              <?php if($this->user->isAdmin()) { ?>
                <li><a href="#" class="albumInviteUploaders" data-id="<?php $this->utility->safe($this->utility->getAttributeFromPath('album')); ?>"><i class="icon-exchange"></i> Invite uploaders</a></li>
              <?php } ?>
            <?php } ?>
          <?php } elseif($this->utility->isActiveTab('upload') && $this->user->isAdmin()) {?>
            <li class="separator-left">Mobile apps </li>
            <li><a href="http://bit.ly/trovebox-for-iphone" title="Download our Trovebox for iOS"><i class="icon-apple"></i> iPhone / iPad</a></li>
            <li><a href="http://bit.ly/trovebox-for-android" title="Download Trovebox for Android"><i class="icon-android"></i> Android</a></li>
          <?php } elseif($this->utility->isActiveTab('manage')) {?>
            <li class="separator-left"><a href="/manage/settings"><i class="icon-cogs"></i> General Settings</a></li>
            <li><a href="/manage/groups/list"><i class="icon-group"></i> Team Management</a></li>
          <?php } ?>
