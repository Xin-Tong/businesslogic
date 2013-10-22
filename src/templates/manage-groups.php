<div class="row">
  <div class="span2">
    <ul class="nav nav-tabs nav-stacked affix sub-navigation">
      <li class="<?php if($page === 'group-list') { ?>active <?php } ?>"><a href="/manage/groups/list"><i class="icon-fixed-width icon-group"></i> Groups</a></li>
      <li class="<?php if($page === 'groups-collaborators') { ?>active <?php } ?>"><a href="/manage/groups/collaborators"><i class="icon-fixed-width icon-user"></i> Collaborators</a></li>
      <li class="<?php if($page === 'administrators') { ?>active <?php } ?>"><a href="/manage/administrators"><i class="icon-fixed-width icon-user-md"></i> Administrators</a></li>
    </ul>
  </div>
  <div class="span10 sections">
    <?php if($page === 'administrators') { ?>
      <div class="row collaborators">
        <div class="span10">
          <form method="post" action="/manage/settings">
            <h2>Administrators</h2>
            <p class="blurb">
              <i class="icon-info-sign"></i> Enter email addresses for others you'd like to grant <strong><em>full</em></strong> access to your account. They'll receive an email to set a password.
            </p>
            <?php for($i=0; $i<$administratorLimit; $i++) { ?>
              <div><input type="text" name="admins[<?php echo $i; ?>]" <?php if(isset($admins[$i])) { ?> value="<?php $this->utility->safe($admins[$i]); ?>" <?php } ?> placeholder="user<?php echo ($i+1); ?>@example.com"></div>
            <?php } ?>
            <div class="btn-toolbar"><button class="btn btn-brand addSpinner">Save</button></div>
            <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
            <input type="hidden" name="skipDefaults" value="1">
            <input type="hidden" name="r" value="/manage/administrators">
          </form>
        </div>
      </div>
    <?php } elseif($page === 'groups-collaborators') { ?>
      <div class="row groups-collaborators">
        <div class="span10">
          <h2>Collaborators Members <small>You're using <?php echo count($members); ?> of <?php $this->utility->safe($collaboratorLimit); ?> collaborators</small></h2>
          <p class="blurb"><i class="icon-info-sign"></i> This is a list of collaborators you've granted access across all of your groups.</p>
          <?php if(empty($members)) { ?>
            You haven't added any collaborators to your groups.
          <?php } else { ?>
            <ul class="collaborators unstyled">
              <?php foreach($members as $memb) { ?>
                <li>
                  <div class="row">
                    <div class="span1 photo">
                      <?php if(isset($memb['photo']) && !empty($memb['photo'])) { ?>
                        <img src="<?php $this->utility->safe($memb['photo']); ?>">
                      <?php } else { ?>
                        <i class="icon-user"></i>
                      <?php } ?>
                    </div>
                    <div class="span9 email"><a href="/manage/groups/collaborator/<?php $this->utility->safe(urlencode($memb['email'])); ?>/view"><?php $this->utility->safe($memb['email']); ?></a></div>
                  </div>
                </li>
              <?php } ?>
            </ul>
          <?php } ?>
        </div>
      </div>
    <?php } elseif($page === 'groups-collaborator') { ?>
      <div class="row groups-collaborator">
        <div class="span10">
          <h2><?php $this->utility->safe($email); ?></h2>
          <p class="blurb"><i class="icon-info-sign"></i> Below is a list of all the groups this collaborator is a member of.</p>
          <?php if(empty($groups)) { ?>
            This collaborator isn't a member of any groups.
          <?php } else { ?>
            <ul class="groups unstyled">
              <?php foreach($groups as $group) { ?>
                <li><a href="/manage/group/<?php $this->utility->safe($group['id']); ?>/view"><?php $this->utility->safe($group['name']); ?></a></li>
              <?php } ?>
            </ul>
          <?php } ?>
        </div>
      </div>
    <?php } elseif($page === 'group-list') { ?>
      <div class="row grouplist">
        <div class="span10">
          <h2>Groups <small><a href="#" class="toggle" data-target="form.groupCreate">Create a group</a></small></h2>
          <form class="groupCreate hide">
            <label>What's the name of your group?</label>
            <input type="text" name="name">

            <label>Describe this group (optional)</label>
            <textarea name="description"></textarea>

            <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
            <input type="hidden" name="httpCodes" value="*">
            <div class="btn-toolbar"><button class="btn btn-brand addSpinner">Create</button></div>
            <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
          </form>
          <?php if(!empty($groups)) { ?>
            <?php foreach($groups as $group) { ?>
              <div class="row group-list">
                <div class="span10">
                  <a href="/manage/group/<?php echo $group['id']; ?>/view"><i class="icon-group"></i> <?php $this->utility->safe($group['name']); ?></a>
                  <p>Contains <?php printf('%d %s', count($group['album']), $this->utility->plural(count($group['album']), 'album', false)); ?>.</p>
                </div>
              </div>
            <?php } ?>
          <?php } else { ?>
            <div class="row">
              <div class="span10 empty">
                <h3>Start now, <a href="#" class="toggle" data-target="form.groupCreate">create a group</a></h3>
              </div>
            </div>
          <?php } ?>
          <!--<table class="table groups hide"></table>
          <div class="no-groups hide">You haven't created any groups yet.</div>-->
        </div>
      </div>
    <?php } elseif($page === 'group-view') { ?>
      <div class="row groupview">
        <div class="span10">
          <div class="group-meta"></div>
          <hr>
          <h3>Members <small><a href="#" class="toggle" data-target="form.groupMemberAdd">Add members</a></small></h3>
          <p class="blurb">
            <i class="icon-info-sign"></i> Members have access to the albums specified by this group.
          </p>
          <!--<form action="/group/<?php $this->utility->safe($group['id']); ?>/update" class="groupUpdateHash hide groupPermissionUser">
            <div class="controls">
              <label class="checkbox inline">
                <input type="checkbox" name="R" value="1" <?php if($group['user'] === true || (isset($group['user']['R']) && $group['user']['R'] === true)) { ?>checked="checked"<?php } ?>>
                View users in this group
              </label>
            </div>
            <div class="controls">
              <label class="checkbox inline">
                <input type="checkbox" name="C" value="1" <?php if($group['user'] === true || (isset($group['user']['C']) && $group['user']['C'] === true)) { ?>checked="checked"<?php } ?>>
                Add users to this group
              </label>
            </div>
            <div class="controls">
              <label class="checkbox inline">
                <input type="checkbox" name="D" value="1" <?php if($group['user'] === true || (isset($group['user']['D']) && $group['user']['D'] === true)) { ?>checked="checked"<?php } ?>>
                Remove users from this group
              </label>
            </div>
            <div class="btn-toolbar"><button class="btn btn-brand addSpinner">Save</button></div>
            <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
            <input type="hidden" name="key" value="user">
          </form>-->
          <div class="row userList">
            <div class="span10">
              <form action="/group/<?php $this->utility->safe($group['id']); ?>/member/add" class="form-inline groupMemberAdd hide">
                <input type="text" placeholder="Email address" name="emails"> <button class="btn btn-brand addSpinner">Add member</button>
                <input type="hidden" name="httpCodes" value="*">
                <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
              </form>
              <ul class="unstyled">
                <?php foreach($group['members'] as $member) { ?>
                  <li data-email="li-<?php $this->utility->safe($member); ?>"><?php $this->utility->safe($member); ?> <a href="#/group/<?php $this->utility->safe($group['id']); ?>/member/remove" class="groupMemberRemove" data-email="<?php $this->utility->safe($member); ?>"><i class="icon-trash"></i> delete</a></li>
                <?php } ?>
              </ul>
            </div>
          </div>
 
          <h3>Albums <small><a href="#" class="toggle" data-target="form.groupAlbumAdd">Add albums</a></small></h3>
          <p class="blurb">
            <i class="icon-info-sign"></i> Members of this group can upload into the albums below.
          </p>
          <div class="row album-header">
            <div class="span10">
              <form action="/group/<?php $this->utility->safe($group['id']); ?>/update" class="form-inline groupAlbumAdd groupUpdateHash hide">
                <select name="albums" class="showGroupAlbumOptions">
                  <option value="">Select an album</option>
                  <?php foreach($allAlbums as $album) { ?>
                    <?php if(!isset($groupAlbums[$album['id']])) { ?>
                      <option value="<?php $this->utility->safe($album['id']); ?>"><?php $this->utility->safe($album['name']); ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
                <input type="hidden" name="R" value="1">
                <input type="hidden" name="C" value="1">
                <!--<span class="checkboxes">
                  <span class="inner">
                    <label class="checkbox inline">
                      <input type="checkbox" name="R" value="1">
                      View
                    </label>
                    <label class="checkbox inline">
                      <input type="checkbox" name="C" value="1">
                      Add
                    </label>
                    <label class="checkbox inline">
                      <input type="checkbox" name="D" value="1">
                      Remove
                    </label>
                  </span>
                </span>-->
                <button class="btn btn-brand addSpinner">Add album</button> <span class="inline-help light"><em>or <a href="#" class="showBatchForm album">create</a> an album</em></span>
                <input type="hidden" name="httpCodes" value="*">
                <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
                <input type="hidden" name="key" value="album-add">
              </form>
            </div>
          </div>
          <div class="row albums">
            <?php foreach($group['album'] as $albumId => $permissions) { ?>
              <div class="span3" data-album="<?php $this->utility->safe($albumId); ?>">
                <div class="cover">
                  <span class="stack stack1"></span>
                  <span class="stack stack2"></span>
                  <?php if(!empty($allAlbums[$albumId]['cover'])) { ?>
                    <img class="img-polaroid" src="<?php $this->utility->safe($allAlbums[$albumId]['cover']['path200x200xCR']); ?>">
                  <?php } else { ?>
                    <img class="img-polaroid" src="data:image/gif;base64,R0lGODlhAQABAPAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==">
                  <?php } ?>
                </div>
              </div>
              <div class="span2" data-album="<?php $this->utility->safe($albumId); ?>">
                <h4 class="name"><?php $this->utility->safe($allAlbums[$albumId]['name']); ?></h4>
                <!--<form action="/group/<?php $this->utility->safe($group['id']); ?>/update" class="groupUpdateHash">
                  <div class="controls">
                    <label class="checkbox inline">
                      <input type="checkbox" name="R" value="1" <?php if($group['album'] === true || (isset($group['album'][$albumId]['R']) && $group['album'][$albumId]['R'] === true)) { ?>checked="checked"<?php } ?>>
                      View this album
                    </label>
                  </div>
                  <div class="controls">
                    <label class="checkbox inline">
                      <input type="checkbox" name="C" value="1" <?php if($group['album'] === true || (isset($group['album'][$albumId]['C']) && $group['album'][$albumId]['C'] === true)) { ?>checked="checked"<?php } ?>>
                      Upload photos
                    </label>
                  </div>
                  <div class="controls">
                    <label class="checkbox inline">
                      <input type="checkbox" name="D" value="1" <?php if($group['album'] === true || (isset($group['album'][$albumId]['D']) && $group['album'][$albumId]['D'] === true)) { ?>checked="checked"<?php } ?>>
                      Remove photos
                    </label>
                  </div>
                  <div class="btn-toolbar"><button class="btn btn-brand addSpinner album-<?php $this->utility->safe($albumId); ?>">Save</button></div>
                  <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
                  <input type="hidden" name="key" value="album">
                  <input type="hidden" name="albumId" value="<?php $this->utility->safe($albumId); ?>">
                </form>-->
                <form action="/group/<?php $this->utility->safe($group['id']); ?>/update" class="form-inline groupUpdateHash">
                  <small class="light">You can <a href="<?php $this->url->albumView($albumId); ?>">view</a> or <button class="btn-link">remove</button> this album</small>
                  <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
                  <input type="hidden" name="key" value="album-remove">
                  <input type="hidden" name="albumId" value="<?php $this->utility->safe($albumId); ?>">
                </form>
              </div>
            <?php } ?>
          </div>
          <hr>
          <div class="group-delete-meta delete-actions"></div>
        </div>
      </div>
      <script>
        var __initData = <?php echo json_encode((object)$group); ?>;
        var __initDataGroupAlbums = <?php echo json_encode((object)$groupAlbums); ?>;
        //var __initDataAllAlbums = <?php echo json_encode((object)$allAlbums); ?>;
      </script>
    <?php } elseif($page === 'user-list') { ?>
      <div class="row userlist">
        <div class="span10">
          <form class="userCreate">
            <h2>Create a new group</h2>
            <label>What's the name of your group?</label>
            <input type="text" name="name">

            <label>Describe this group (optional)</label>
            <textarea name="description"></textarea>

            <input type="hidden" name="crumb" value="<?php $this->utility->safe($crumb); ?>">
            <div class="btn-toolbar"><button class="btn btn-brand">Create</button></div>
          </form>

          <h2>View and edit your users <small>(<a href="#" class="toggle" data-target="form.groupCreate"><i class="icon-plus-sign-alt"></i> New group</a>)</small></h2>
          <table class="table groups hide"></table>
          <div class="no-groups hide">You haven't created any groups yet.</div>
        </div>
      </div>
    <?php } ?>
    <!--<div class="row settings groupform">
      <div class="span10">
      </div>
    </div>-->
  </div>
</div>
