<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015151201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_users DROP CONSTRAINT fk_839829064b89032c');
        $this->addSql('ALTER TABLE post_users DROP CONSTRAINT fk_8398290667b3b43d');
        $this->addSql('DROP TABLE post_users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE post_users (post_id INT NOT NULL, users_id INT NOT NULL, PRIMARY KEY(post_id, users_id))');
        $this->addSql('CREATE INDEX idx_8398290667b3b43d ON post_users (users_id)');
        $this->addSql('CREATE INDEX idx_839829064b89032c ON post_users (post_id)');
        $this->addSql('ALTER TABLE post_users ADD CONSTRAINT fk_839829064b89032c FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_users ADD CONSTRAINT fk_8398290667b3b43d FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
