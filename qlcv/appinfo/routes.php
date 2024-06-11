<?php

declare(strict_types=1);

// Copyright Text: Lucy <ct040407@actv.edu.vn>
// License: AGPL-3.0-or-later

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\QLCV\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [

        // Page
        ['name' => 'test#predictCompletionTime', 'url' => '/predictCompletionTime', 'verb' => 'GET'],

        // Page
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // Data
        ['name' => 'Data#countWorksPerProject', 'url' => '/data/count_works/{userId}', 'verb' => 'GET'],

        //User
        ['name' => 'User#getUsers', 'url' => '/users', 'verb' => 'GET'],
        ['name' => 'User#getFullName', 'url' => '/full_name/{qlcb_uid}', 'verb' => 'GET'],

        // File
        ['name' => 'file#uploadFile', 'url' => '/upload_file', 'verb' => 'POST'],
        ['name' => 'file#deleteFile', 'url' => '/delete_file/{file_id}/{owner}', 'verb' => 'DELETE'],
        ['name' => 'file#downloadFile', 'url' => '/download_file', 'verb' => 'GET'],
        ['name' => 'file#getFiles', 'url' => '/get_files', 'verb' => 'GET'],
        ['name' => 'file#createFile', 'url' => '/create_file', 'verb' => 'POST'],
        ['name' => 'file#shareFolder', 'url' => '/share_folder', 'verb' => 'GET'],
        ['name' => 'file#setReadPermission', 'url' => '/set_read_permission', 'verb' => 'GET'],
        ['name' => 'file#addCreatePermission', 'url' => '/add_create_permission', 'verb' => 'GET'],

        // Project
        ['name' => 'Project#createProject', 'url' => '/create_project', 'verb' => 'POST'],
        ['name' => 'Project#getProjects', 'url' => '/projects', 'verb' => 'GET'],
        ['name' => 'Project#getAProject', 'url' => '/project/{project_id}', 'verb' => 'GET'],
        ['name' => 'Project#updateProject', 'url' => '/update_project', 'verb' => 'PUT'],
        ['name' => 'Project#deleteProject', 'url' => '/delete_project/{project_id}', 'verb' => 'DELETE'],

        // Work
        ['name' => 'Work#createWorkAndTasks', 'url' => '/create_work', 'verb' => 'POST'],
        ['name' => 'Work#getWorks', 'url' => '/works', 'verb' => 'GET'],
        ['name' => 'Work#getWorkById', 'url' => '/work_by_id/{work_id}', 'verb' => 'GET'],
        ['name' => 'Work#updateWork', 'url' => '/update_work', 'verb' => 'PUT'],
        ['name' => 'Work#deleteWork', 'url' => '/delete_work/{work_id}', 'verb' => 'DELETE'],

        // Comment
        ['name' => 'Comment#createComment', 'url' => '/create_comment', 'verb' => 'POST'],
        ['name' => 'Comment#getComments', 'url' => '/comments/{work_id}', 'verb' => 'GET'],

        // Task
        ['name' => 'Task#getTasks', 'url' => '/tasks/{work_id}', 'verb' => 'GET'],
        ['name' => 'Task#createTask', 'url' => '/create_task', 'verb' => 'POST'],
        ['name' => 'Task#updateTask', 'url' => '/update_task', 'verb' => 'PUT'],
        ['name' => 'Task#deleteTask', 'url' => '/delete_task/{task_id}', 'verb' => 'DELETE'],

    ],
];