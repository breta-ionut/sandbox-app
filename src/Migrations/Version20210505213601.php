<?php

declare(strict_types=1);

namespace AppMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210505213601 extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE image (id SERIAL NOT NULL, token TEXT NOT NULL, path TEXT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045F5F37A13B ON image (token)');
        $this->addSql('CREATE TABLE token (id SERIAL NOT NULL, user_id INT DEFAULT NULL, token TEXT NOT NULL, expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F37A13B5F37A13B ON token (token)');
        $this->addSql('CREATE INDEX IDX_5F37A13BA76ED395 ON token (user_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, first_name TEXT NOT NULL, last_name TEXT NOT NULL, email TEXT NOT NULL, roles JSON NOT NULL, password TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * {@inheritDoc}
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE token DROP CONSTRAINT FK_5F37A13BA76ED395');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP TABLE "user"');
    }
}
