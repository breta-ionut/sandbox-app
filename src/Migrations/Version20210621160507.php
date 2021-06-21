<?php

declare(strict_types=1);

namespace AppMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210621160507 extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tokens ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE users ADD image_id INT NOT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E93DA5256D FOREIGN KEY (image_id) REFERENCES images (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E93DA5256D ON users (image_id)');
    }

    /**
     * {@inheritDoc}
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E93DA5256D');
        $this->addSql('DROP INDEX UNIQ_1483A5E93DA5256D');
        $this->addSql('ALTER TABLE users DROP image_id');
        $this->addSql('ALTER TABLE tokens ALTER user_id DROP NOT NULL');
    }
}
