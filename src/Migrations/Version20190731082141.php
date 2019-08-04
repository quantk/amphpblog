<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190731082141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $usersTable = $schema->createTable('users');
        $usersTable->addColumn('id', Type::INTEGER, [
            'autoincrement' => true
        ]);
        $usersTable->addColumn('username', Type::STRING, [
            'length' => 255
        ]);
        $usersTable->addColumn('password', Type::STRING, [
            'length' => 255
        ]);
        $usersTable->setPrimaryKey(['id']);
        $usersTable->addUniqueIndex(['username']);

        $projectsTable = $schema->createTable('projects');
        $projectsTable->addColumn('id', Type::INTEGER, [
            'autoincrement' => true
        ]);
        $projectsTable->addColumn('title', Type::STRING, [
            'length' => 255
        ]);
        $projectsTable->addColumn('text', Type::TEXT);
        $projectsTable->setPrimaryKey(['id']);

        $notesTable = $schema->createTable('notes');
        $notesTable->addColumn('id', Type::INTEGER, [
            'autoincrement' => true
        ]);
        $notesTable->addColumn('title', Type::STRING, [
            'length' => 255
        ]);
        $notesTable->addColumn('text', Type::TEXT);
        $notesTable->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
        $schema->dropTable('projects');
        $schema->dropTable('notes');
    }
}
