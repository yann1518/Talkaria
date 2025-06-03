<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603155522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962AD5E258C5');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AD5E258C5 FOREIGN KEY (posts_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT fk_5f9e962ad5e258c5');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT fk_5f9e962ad5e258c5 FOREIGN KEY (posts_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
