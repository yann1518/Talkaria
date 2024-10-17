<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008121530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users_comments (users_id INT NOT NULL, comments_id INT NOT NULL, PRIMARY KEY(users_id, comments_id))');
        $this->addSql('CREATE INDEX IDX_24DFBDF067B3B43D ON users_comments (users_id)');
        $this->addSql('CREATE INDEX IDX_24DFBDF063379586 ON users_comments (comments_id)');
        $this->addSql('ALTER TABLE users_comments ADD CONSTRAINT FK_24DFBDF067B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_comments ADD CONSTRAINT FK_24DFBDF063379586 FOREIGN KEY (comments_id) REFERENCES comments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE users_comments DROP CONSTRAINT FK_24DFBDF067B3B43D');
        $this->addSql('ALTER TABLE users_comments DROP CONSTRAINT FK_24DFBDF063379586');
        $this->addSql('DROP TABLE users_comments');
    }
}
