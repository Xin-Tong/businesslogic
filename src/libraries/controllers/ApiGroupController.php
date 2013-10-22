<?php
/**
  * Group controller for API endpoints.
  *
  * @author Jaisen Mathai <jaisen@jmathai.com>
 */
class ApiGroupController extends ApiBaseController
{
  private $group;

  /**
    * Call the parent constructor
    *
    * @return void
    */
  public function __construct()
  {
    parent::__construct();
    $this->group = new Group;
  }

  /**
    * Create a new group
    * Returns the newly created group or false as the response data
    *
    * @return string Standard JSON envelope
    */
  public function create()
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();
    $groupId = $this->group->create($_POST);

    if($groupId !== false)
    {
      $res = $this->api->invoke(sprintf('/%s/group/%s/view.json', $this->apiVersion, $groupId), EpiRoute::httpGet);
      if($res['code'] === 200)
        return $this->created('Group successfully created', $res['result']);
    }

    return $this->error('Could not create a group', false);
  }

  /**
    * Delete a group
    *
    * @return string Standard JSON envelope
    */
  public function delete($id)
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();
    $res = $this->group->delete($id);

    if($res === false)
      return $this->error('Could not delete group', false);

    return $this->noContent('Successfully deleted group', true);
  }

  public function form()
  {
    $template = $this->template->get(sprintf('%s/manage-group-form.php', $this->config->paths->templates));;
    return $this->success('Group form', array('markup' => $template));
  }

  /**
    * Get the owner's groups
    *
    * @return string Standard JSON envelope
    */
  public function list_()
  {
    getAuthentication()->requireAuthentication();

    $userObj = new User;
    if(!$userObj->isAdmin())
      return $this->forbidden('You do not have permission to access this API.', false);

    $groups = $this->group->getGroups();
    if($groups === false)
      return $this->error('An error occurred trying to get your groups', false);

    return $this->success('A list of your groups', (array)$groups);
  }

  /**
    * Manage members in a group
    *
    * @return string Standard JSON envelope
    */
  public function manageMembers($id, $action)
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();

    if(empty($_POST['emails']))
      return $this->error('No email addresses provided', false);

    $emails = (array)explode(',', $_POST['emails']);

    // if we're adding new members we want to check if this request exceeds the limit
    if($action == 'add')
    {
      $planObj = new Plan;
      $collaboratorLimit = $planObj->getCollaboratorLimit();
      $currentMemberCount = count($this->group->getMembersAcrossGroups());
      $countMembersToAdd = count($this->group->getNewMembersFromList($emails));
      if(($currentMemberCount + $countMembersToAdd) > $collaboratorLimit)
        return $this->exceeded('Collaborator limit exceeded', array('limit' => $collaboratorLimit));
    }

    $res = $this->group->manageMembers($id, $emails, $action);

    if($res === false)
      return $this->error('Could not update group member list', false);

    return $this->success('Group members updated', $emails);
  }

  public function members()
  {
    getAuthentication()->requireAuthentication();
    $emails = $this->group->getMembersAcrossGroups();
    if(!$emails)
      return $this->error('Could not get members', false);

    return $this->success('Members across groups', $emails);
  }

  public function memberView($email)
  {
    getAuthentication()->requireAuthentication();
    $groups = $this->group->getGroupsByMember($email);
    return $this->success('Groups for this user', $groups);
  }

  /**
    * Undelete a group
    *
    * @return string Standard JSON envelope
    */
  public function undelete($id)
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();
    $res = $this->group->undelete($id);

    if($res === false)
      return $this->error('Could not undelete group', false);

    return $this->success('Successfully undeleted group', true);
  }

  /**
    * Update an existing group
    * Returns the newly created group or false as the response data
    *
    * @return string Standard JSON envelope
    */
  public function update($id)
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();
    $res = $this->group->update($id, $_POST);

    if($res)
    {
      $group = $this->api->invoke("/{$this->apiVersion}/group/{$id}/view.json", EpiRoute::httpGet);
      return $this->success("Updated group {$id}.", $group['result']);
    }

    return $this->error('Could not update this group.', false);
  }

  /**
    * Get the owner's group as specified by the groupId
    *
    * @param string $id The id of the group
    * @return string Standard JSON envelope
    */
  public function view($id)
  {
    getAuthentication()->requireAuthentication();

    $group = $this->group->getGroup($id);
    if($group === false)
      return $this->error('An error occurred trying to get your group', false);

    return $this->success('Your group', (array)$group);
  }
}
