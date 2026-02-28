<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial migration: creates newsletter and subscriber tables.
 */
final class Version20260228035021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create newsletter and subscriber tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE newsletter (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, content CLOB NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, sent_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE TABLE subscriber (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL, subscribed_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD005B69E7927C74 ON subscriber (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE newsletter');
        $this->addSql('DROP TABLE subscriber');
    }
}

