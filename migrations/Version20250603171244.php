<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter le champ likes à la table post (NOT NULL, DEFAULT 0)
 */
final class Version20250603171244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne likes à la table post, initialise à 0, et rend NOT NULL';
    }

    public function up(Schema $schema): void
    {
        // Ajoute la colonne likes (nullable pour éviter l'erreur si des posts existent déjà)
        $this->addSql('ALTER TABLE post ADD likes INT DEFAULT 0');
        // Met tous les anciens posts à 0 si besoin
        $this->addSql('UPDATE post SET likes = 0 WHERE likes IS NULL');
        // Rend la colonne NOT NULL
        $this->addSql('ALTER TABLE post ALTER likes SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Supprime la colonne likes
        $this->addSql('ALTER TABLE post DROP COLUMN likes');
    }
}