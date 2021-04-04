<?php

declare(strict_types=1);

namespace AppMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210404205527 extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image ADD token CHAR(32) NOT NULL, ADD created_at DATETIME NOT NULL, DROP type');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045F5F37A13B ON image (token)');
        $this->addSql('ALTER TABLE token CHANGE token token CHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    /**
     * {@inheritDoc}
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_C53D045F5F37A13B ON image');
        $this->addSql('ALTER TABLE image ADD type SMALLINT UNSIGNED NOT NULL, DROP token, DROP created_at');
        $this->addSql('ALTER TABLE token CHANGE token token VARCHAR(128) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }
}
