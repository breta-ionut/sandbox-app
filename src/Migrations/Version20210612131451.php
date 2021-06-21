<?php

declare(strict_types=1);

namespace AppMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210612131451 extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE images (id SERIAL NOT NULL, token TEXT NOT NULL, path TEXT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E01FBE6A5F37A13B ON images (token)');
        $this->addSql('CREATE TABLE tokens (id SERIAL NOT NULL, user_id INT DEFAULT NULL, token TEXT NOT NULL, expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AA5A118E5F37A13B ON tokens (token)');
        $this->addSql('CREATE INDEX IDX_AA5A118EA76ED395 ON tokens (user_id)');
        $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, first_name TEXT NOT NULL, last_name TEXT NOT NULL, email TEXT NOT NULL, roles JSON NOT NULL, password TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('ALTER TABLE tokens ADD CONSTRAINT FK_AA5A118EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * {@inheritDoc}
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tokens DROP CONSTRAINT FK_AA5A118EA76ED395');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE tokens');
        $this->addSql('DROP TABLE users');
    }
}
