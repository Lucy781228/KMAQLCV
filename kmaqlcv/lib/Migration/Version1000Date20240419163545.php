<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2024 Your name <your@email.com>
 *
 * @author Your name <your@email.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\KMAQLCV\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version1000Date20240419163545 extends SimpleMigrationStep
{
    /**
     * @param IOutput $output
     * @param Closure(): ISchemaWrapper $schemaClosure
     * @param array $options
     */
    public function preSchemaChange(
        IOutput $output,
        Closure $schemaClosure,
        array $options
    ): void {
    }

    /**
     * @param IOutput $output
     * @param Closure(): ISchemaWrapper $schemaClosure
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(
        IOutput $output,
        Closure $schemaClosure,
        array $options
    ): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // project
        if (!$schema->hasTable("kmaqlcv_project")) {
            $table = $schema->createTable("kmaqlcv_project");
            $table->addColumn("project_id", "integer", [
                "autoincrement" => true,
                "unsigned" => true,
            ]);
            $table->addColumn("project_name", "string", [
                "notnull" => true,
                "length" => 64,
            ]);
            $table->addColumn("user_id", "string", [
                "notnull" => true,
                "length" => 64,
            ]);
            $table->addColumn("start_date", "date", [
                "notnull" => true,
            ]);
            $table->addColumn("end_date", "date", [
                "notnull" => true,
            ]);
            $table->addColumn("status", "smallint", [
                "unsigned" => true,
                "default" => 0
            ]);

            $table->setPrimaryKey(["project_id"]);
        }

        // work
        if (!$schema->hasTable("kmaqlcv_work")) {
            $table = $schema->createTable("kmaqlcv_work");
            $table->addColumn("work_id", "integer", [
                "autoincrement" => true,
                "unsigned" => true,
            ]);
            $table->addColumn("project_id", "integer", [
                "notnull" => true,
                "unsigned" => true,
            ]);
            $table->addColumn("work_name", "string", [
                "notnull" => false,
                "length" => 64,
            ]);
            $table->addColumn("description", "string", [
                "notnull" => false,
                "length" => 1000,
            ]);
            $table->addColumn("status", "smallint", [
                "unsigned" => true,
                "default" => 0
            ]);
            $table->addColumn("start_date", "date", [
                "notnull" => true,
            ]);
            $table->addColumn("end_date", "date", [
                "notnull" => true,
            ]);
            $table->addColumn("label", "string", [
                "notnull" => false,
                "length" => 64,
            ]);
            $table->addColumn("assigned_to", "string", [
                "notnull" => true,
                "length" => 64,
            ]);

            $table->addColumn("owner", "string", [
                "notnull" => true,
                "length" => 64,
            ]);

            $table->setPrimaryKey(["work_id"]);
            $table->addIndex(['project_id'], 'project_index');
        }

        // task
        if (!$schema->hasTable("kmaqlcv_task")) {
            $table = $schema->createTable("kmaqlcv_task");
            $table->addColumn("task_id", "integer", [
                "autoincrement" => true,
                "unsigned" => true,
            ]);
            $table->addColumn("content", "string", [
                "notnull" => true,
                "length" => 255,
            ]);
            $table->addColumn("work_id", "integer", [
                "notnull" => true,
                "unsigned" => true,
            ]);
            $table->addColumn("is_done", "boolean", [
                "notnull" => false,
            ]);
            $table->setPrimaryKey(["task_id"]);
            $table->addIndex(['work_id'], 'work_index');
        }

        // file
        if (!$schema->hasTable("kmaqlcv_file")) {
            $table = $schema->createTable("kmaqlcv_file");
            $table->addColumn("file_id", "integer", [
                "unsigned" => true,
            ]);
            $table->addColumn("work_id", "integer", [
                "notnull" => true,
                "unsigned" => true,
            ]);

            $table->setPrimaryKey(["file_id"]);
            $table->addIndex(['work_id'], 'work_index');
        }

        // comment
        if (!$schema->hasTable("kmaqlcv_comment")) {
            $table = $schema->createTable("kmaqlcv_comment");
            $table->addColumn("comment_id", "integer", [
                "unsigned" => true,
                "autoincrement" => true,
            ]);
            $table->addColumn("work_id", "integer", [
                "notnull" => true,
                "unsigned" => true,
            ]);
            $table->addColumn("user_id", "string", [
                "notnull" => true,
                "length" => 64,
            ]);
            $table->addColumn("message", "string", [
                "notnull" => true,
                "length" => 1000,
            ]);
            $table->addColumn("reply_comment_id", "integer", [
                "unsigned" => true,
                "default" => 0
            ]);
            $table->setPrimaryKey(["comment_id"]);
            $table->addIndex(['work_id'], 'work_index');
        }

        $project = $schema->getTable('kmaqlcv_project');
        $work = $schema->getTable('kmaqlcv_work');
		$work->addForeignKeyConstraint($project, ['project_id'], ['project_id'], ['onDelete' => 'CASCADE']);

        $task = $schema->getTable('kmaqlcv_task');
		$task->addForeignKeyConstraint($work, ['work_id'], ['work_id'], ['onDelete' => 'CASCADE']);

        $comment = $schema->getTable('kmaqlcv_comment');
		$comment->addForeignKeyConstraint($work, ['work_id'], ['work_id'], ['onDelete' => 'CASCADE']);

        $file = $schema->getTable('kmaqlcv_file');
		$file->addForeignKeyConstraint($work, ['work_id'], ['work_id'], ['onDelete' => 'CASCADE']);

        return $schema;
    }

    /**
     * @param IOutput $output
     * @param Closure(): ISchemaWrapper $schemaClosure
     * @param array $options
     */
    public function postSchemaChange(
        IOutput $output,
        Closure $schemaClosure,
        array $options
    ): void {
    }
}