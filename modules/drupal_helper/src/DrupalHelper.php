<?php

/**
 * Created by PhpStorm.
 * User: USER
 * Date: 6/7/18
 * Time: 5:47 PM
 */
namespace Drupal\drupal_helper;


class DrupalHelper extends DrupalQuery
{
    public $helper;
    public $views;
    public $crud;
    public $form;
    public $query;
    public $user;
    public $node;


    public function __construct()
    {
        $this->helper = new DrupalCommonHelper();
        $this->crud = new DrupalCRUD();
        $this->views = new DrupalViewsHelper();
        $this->form = new DrupalFormAPI();
        $this->query = new DrupalQuery();
        $this->user = new DrupalUser();
        $this->node = new DrupalNodeHelper();
        parent::__construct();
    }

    public function cronjobGitRepo(){
        $config = \Drupal::config('drupal_helper.gitrepo');
        $repositories = $config->get('list');
        if($repositories){
            foreach ($repositories as $repository_path) {
              $commit_message = "Automated commit from Drupal"; // Customize your commit message
              $branch = 'server'; // Specify the branch to push to
              // Navigate to the repository directory and execute git commands
              $repository_path = DRUPAL_ROOT.$repository_path ;
              $commands = [
                  "cd $repository_path",
                  "(git branch --list $branch && git checkout $branch) || git checkout -b $branch",
                  "git pull --force",
                  "git add -A", // Adds all changes, including new files and deletions
                  "git commit -m '$commit_message'",
                  "git push --force origin $branch" // Force push to the specified branch
              ];
              $command = implode(" && ", $commands);
              exec($command, $output, $return_var);
      
              // Optionally log the output for each repository
              if ($return_var !== 0) {
                  \Drupal::logger('drupal_helper')->error('Git operation failed in ' . $repository_path . ' with message: @message', ['@message' => implode("\n", $output)]);
              } else {
                  \Drupal::logger('drupal_helper')->notice('Git operations executed successfully in ' . $repository_path);
              }
            }
         }
    }
 
}