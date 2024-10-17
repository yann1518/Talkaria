<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008120826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_users (post_id INT NOT NULL, users_id INT NOT NULL, PRIMARY KEY(post_id, users_id))');
        $this->addSql('CREATE INDEX IDX_839829064B89032C ON post_users (post_id)');
        $this->addSql('CREATE INDEX IDX_8398290667B3B43D ON post_users (users_id)');
        $this->addSql('ALTER TABLE post_users ADD CONSTRAINT FK_839829064B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_users ADD CONSTRAINT FK_8398290667B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users DROP comments');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_users DROP CONSTRAINT FK_839829064B89032C');
        $this->addSql('ALTER TABLE post_users DROP CONSTRAINT FK_8398290667B3B43D');
        $this->addSql('DROP TABLE post_users');
        $this->addSql('ALTER TABLE users ADD comments VARCHAR(255) NOT NULL');
    }
}
